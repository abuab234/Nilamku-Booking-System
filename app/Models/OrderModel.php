<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * OrderModel
 *
 * Mengelola siklus hidup pesanan dari pembuatan hingga penyelesaian.
 *
 * Method kritis createBooking() mengimplementasikan transaksi atomik
 * yang menggabungkan: row locking slot → insert order → insert items → insert history.
 * Satu kegagalan di mana pun akan me-rollback seluruh operasi.
 */
class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nomor_antrian',
        'user_id',
        'timeslot_id',
        'status',
        'total_harga',
        'catatan',
        'alasan_batal',
        'metode_layanan',
        'alamat_pengiriman',
        'jarak_km',
        'biaya_ongkir',
        'metode_pembayaran',
        'bukti_transfer',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id'     => 'required|integer|greater_than[0]',
        'timeslot_id' => 'permit_empty|integer',
        'status'      => 'required|in_list[pending,diproses,siap,selesai,dibatalkan]',
        'total_harga' => 'required|decimal|greater_than_equal_to[0]',
    ];

    /** Menyimpan pesan error dari createBooking() untuk dikonsumsi Controller */
    public string $bookingError = '';

    // ──────────────────────────────────────────────────────────────────────
    // ★ METODE UTAMA — TRANSAKSI ATOMIK
    // ──────────────────────────────────────────────────────────────────────

    /**
     * createBooking() — Membuat pesanan baru secara atomik.
     *
     * Alur transaksi (semua-atau-tidak-ada):
     *   1. transStart()           → mulai transaksi database
     *   2. lockAndConsumeSlot()   → (opsional jika slotId ada)
     *   3. Hitung nomor antrean & ongkir
     *   4. Insert orders          → buat record pesanan (status: pending)
     *   5. insertBatchForOrder()  → insert semua order_items sekaligus
     *   6. recordTransition()     → catat status awal di audit trail
     *   7. transComplete()        → COMMIT jika semua sukses
     *
     * @param int         $userId
     * @param int|null    $slotId          ID slot waktu (opsional, jika tanpa slot langsung diproses)
     * @param array       $cartItems
     * @param float       $totalHarga
     * @param string      $catatan
     * @param string      $metodeLayanan   'dine_in'|'delivery'
     * @param string|null $alamatPengiriman
     * @param float       $jarakKm
     * @param string      $metodePembayaran 'cash'|'transfer'
     * @param string|null $buktiTransfer    Path file gambar bukti transfer
     * @return int|false  Order ID jika berhasil, false jika gagal
     */
    public function createBooking(
        int     $userId,
        ?int    $slotId = null,
        array   $cartItems = [],
        float   $totalHarga = 0.00,
        string  $catatan = '',
        string  $metodeLayanan = 'dine_in',
        ?string $alamatPengiriman = null,
        float   $jarakKm = 0.00,
        string  $metodePembayaran = 'transfer',
        ?string $buktiTransfer = null
    ): int|false {
        $this->db->transException(true);
        $this->db->transStart();

        try {
            // ── Step 1: LOCK & CONSUME SLOT (Jika slotId disertakan) ───────────
            if ($slotId !== null && $slotId > 0) {
                $timeSlotModel = new TimeSlotModel();
                if (! $timeSlotModel->lockAndConsumeSlot($slotId)) {
                    $this->db->transRollback();
                    $this->bookingError = 'Slot waktu yang dipilih sudah penuh atau tidak tersedia. Silakan pilih slot lain.';
                    log_message('notice', "[OrderModel::createBooking] Slot {$slotId} penuh/tidak ada. User: {$userId}");
                    return false;
                }
            }

            // ── Step 2: HITUNG ONGKIR & TOTAL HARGA ───────────
            $subtotalItems = 0.00;
            foreach ($cartItems as $it) {
                $subtotalItems += (float) ($it['subtotal'] ?? 0);
            }

            $biayaOngkir = 0.00;
            if ($metodeLayanan === 'delivery') {
                if ($jarakKm >= 1.0) {
                    $biayaOngkir = (float) (ceil($jarakKm / 5.0) * 8000);
                } else {
                    $biayaOngkir = 0.00;
                }
            } else {
                $metodeLayanan = 'dine_in';
                $alamatPengiriman = null;
                $jarakKm = 0.00;
                $biayaOngkir = 0.00;
            }

            // Total harga final adalah subtotal item + biaya ongkir
            $finalTotalHarga = $subtotalItems + $biayaOngkir;

            // ── Step 3: GENERATE NOMOR ANTREAN HARIAN ─────────
            // Format: A-001, A-002, dst. berbasis jumlah pesanan pada tanggal hari ini
            $today = date('Y-m-d');
            $countToday = $this->where('DATE(created_at)', $today)->countAllResults();
            $nomorAntrian = 'A-' . str_pad((string) ($countToday + 1), 3, '0', STR_PAD_LEFT);

            // ── Step 4: INSERT ORDER ───────────────────────────
            $orderId = $this->insert([
                'nomor_antrian'     => $nomorAntrian,
                'user_id'           => $userId,
                'timeslot_id'       => ($slotId !== null && $slotId > 0) ? $slotId : null,
                'status'            => 'pending',
                'total_harga'       => $finalTotalHarga,
                'catatan'           => $catatan !== '' ? $catatan : null,
                'metode_layanan'    => $metodeLayanan,
                'alamat_pengiriman' => $alamatPengiriman,
                'jarak_km'          => $jarakKm,
                'biaya_ongkir'      => $biayaOngkir,
                'metode_pembayaran' => $metodePembayaran,
                'bukti_transfer'    => $buktiTransfer,
            ]);

            if (! $orderId) {
                throw new \RuntimeException('Gagal menyimpan data pesanan ke database.');
            }

            // ── Step 5: INSERT ORDER ITEMS (batch) ─────────────
            $orderItemModel = new OrderItemModel();

            if (! $orderItemModel->insertBatchForOrder((int) $orderId, $cartItems)) {
                throw new \RuntimeException('Gagal menyimpan detail item pesanan.');
            }

            // ── Step 6: INSERT STATUS HISTORY (audit trail) ────
            $historyModel = new OrderStatusHistoryModel();
            $historyId    = $historyModel->recordTransition(
                orderId:    (int) $orderId,
                statusLama: null,
                statusBaru: 'pending',
                diubahOleh: $userId,
                catatan:    'Pesanan baru dibuat oleh pelanggan (' . strtoupper($metodeLayanan) . ').'
            );

            if (! $historyId) {
                throw new \RuntimeException('Gagal menyimpan riwayat status pesanan.');
            }

            // ── Step 7: COMMIT ─────────────────────────────────
            $this->db->transComplete();

            log_message('info', "[OrderModel::createBooking] Order #{$orderId} ({$nomorAntrian}) berhasil dibuat. Total: {$finalTotalHarga}");

            return (int) $orderId;

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            $this->bookingError = 'Terjadi kesalahan database: ' . $e->getMessage();
            log_message('error', "[OrderModel::createBooking] DatabaseException → ROLLBACK. Error: " . $e->getMessage());
            return false;

        } catch (\RuntimeException $e) {
            $this->db->transRollback();
            $this->bookingError = $e->getMessage();
            log_message('error', "[OrderModel::createBooking] RuntimeException → ROLLBACK. Error: " . $e->getMessage());
            return false;

        } catch (\Throwable $e) {
            $this->db->transRollback();
            $this->bookingError = 'Terjadi kesalahan sistem yang tidak terduga.';
            log_message('critical', "[OrderModel::createBooking] Unexpected error → ROLLBACK. Error: " . $e->getMessage());
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Method Pendukung
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Update status pesanan sesuai state machine dan catat di history.
     * Ini juga menjadi trigger untuk webhook notification (Fase 5).
     *
     * @param int         $orderId
     * @param string      $statusBaru
     * @param int         $diubahOleh  user_id yang melakukan perubahan
     * @param string      $catatan     alasan perubahan (opsional)
     * @param string|null $alasanBatal alasan spesifik pembatalan jika status baru adalah dibatalkan
     * @return bool
     */
    public function updateOrderStatus(int $orderId, string $statusBaru, int $diubahOleh, string $catatan = '', ?string $alasanBatal = null): bool
    {
        $order = $this->find($orderId);

        if ($order === null) {
            log_message('warning', "[OrderModel::updateOrderStatus] Order #{$orderId} tidak ditemukan.");
            return false;
        }

        // Validasi transisi state machine
        if (! $this->isValidTransition($order->status, $statusBaru)) {
            log_message('warning', "[OrderModel::updateOrderStatus] Transisi tidak valid: {$order->status} → {$statusBaru}");
            return false;
        }

        $this->db->transException(true);
        $this->db->transStart();

        try {
            // Update status order
            $updateData = ['status' => $statusBaru];
            if ($statusBaru === 'dibatalkan' && ! empty($alasanBatal)) {
                $updateData['alasan_batal'] = $alasanBatal;
            }
            $this->update($orderId, $updateData);

            // Rekam ke history
            $historyNote = ! empty($alasanBatal) ? "Dibatalkan: {$alasanBatal}" : ($catatan !== '' ? $catatan : null);
            $historyModel = new OrderStatusHistoryModel();
            $historyModel->recordTransition(
                orderId:    $orderId,
                statusLama: $order->status,
                statusBaru: $statusBaru,
                diubahOleh: $diubahOleh,
                catatan:    $historyNote
            );

            // Jika status selesai, sinkronkan ke buku kas permanen (financial_records)
            if ($statusBaru === 'selesai') {
                $financialModel = new FinancialRecordModel();
                $financialModel->recordFromOrder($orderId);
            }

            // Jika status dibatalkan, HAPUS dari buku kas agar tidak masuk hitungan pendapatan
            // (safeguard: jika pesanan sempat direkam sebelumnya)
            if ($statusBaru === 'dibatalkan') {
                $financialModel = new FinancialRecordModel();
                $financialModel->removeByOrderId($orderId);
            }

            // Trigger webhook ke tabel webhook_logs
            \App\Libraries\WebhookService::queueNotification(
                $orderId, 
                "order.status_{$statusBaru}", 
                [
                    'order_id'    => $orderId,
                    'status_lama' => $order->status,
                    'status_baru' => $statusBaru,
                    'diubah_oleh' => $diubahOleh,
                ]
            );

            $this->db->transComplete();

            return true;

        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', "[OrderModel::updateOrderStatus] ROLLBACK. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validasi apakah transisi status sesuai state machine yang didefinisikan di spec.
     *
     * @param string $dari  Status saat ini
     * @param string $ke    Status tujuan
     * @return bool
     */
    private function isValidTransition(string $dari, string $ke): bool
    {
        $stateMap = [
            'pending'   => ['diproses', 'dibatalkan'],
            'diproses'  => ['siap', 'dibatalkan'],
            'siap'      => ['selesai'],
            'selesai'   => [],              // Final state — tidak bisa berubah
            'dibatalkan'=> [],              // Final state — tidak bisa berubah
        ];

        return in_array($ke, $stateMap[$dari] ?? [], true);
    }

    /**
     * Ambil order detail lengkap beserta items dan slot info.
     *
     * @param int $orderId
     * @return object|null
     */
    public function getOrderDetail(int $orderId): ?object
    {
        $order = $this->find($orderId);

        if ($order === null) {
            return null;
        }

        // Attach items
        $orderItemModel  = new OrderItemModel();
        $order->items    = $orderItemModel->getItemsByOrderId($orderId);

        // Attach history
        $historyModel    = new OrderStatusHistoryModel();
        $order->history  = $historyModel->getHistoryByOrderId($orderId);

        // Attach slot info (jika ada)
        $timeSlotModel   = new TimeSlotModel();
        $order->slot     = !empty($order->timeslot_id) ? $timeSlotModel->find((int) $order->timeslot_id) : null;

        // Attach user info
        $userModel       = new UserModel();
        $order->user     = $userModel->find((int) $order->user_id);

        return $order;
    }

    /**
     * Ambil daftar pesanan untuk Dasbor Admin beserta user, timeslot, dan items.
     * Menggunakan grouping batch untuk items guna menghindari N+1 query.
     *
     * @param int $limit
     * @return array<object>
     */
    public function getOrdersForDashboard(int $limit = 50): array
    {
        $orders = $this->select('orders.*, users.nama as user_name, users.email as user_email, users.no_telepon as user_hp, time_slots.tanggal as slot_tanggal, time_slots.jam_mulai as slot_mulai, time_slots.jam_selesai as slot_selesai')
                       ->join('users', 'users.id = orders.user_id', 'left')
                       ->join('time_slots', 'time_slots.id = orders.timeslot_id', 'left')
                       ->orderBy('orders.created_at', 'DESC')
                       ->findAll($limit);

        if (empty($orders)) {
            return [];
        }

        $orderIds = array_map(static fn($o) => (int) $o->id, $orders);

        $orderItems = $this->db->table('order_items oi')
                               ->select('oi.*, i.nama as item_nama')
                               ->join('items i', 'i.id = oi.item_id', 'left')
                               ->whereIn('oi.order_id', $orderIds)
                               ->get()
                               ->getResult();

        $itemsByOrder = [];
        foreach ($orderItems as $item) {
            $itemsByOrder[$item->order_id][] = $item;
        }

        foreach ($orders as $order) {
            $order->items = $itemsByOrder[$order->id] ?? [];
        }

        return $orders;
    }

    /**
     * Ambil ringkasan statistik untuk Dasbor Admin:
     * - total pesanan hari ini
     * - pesanan pending
     * - total pendapatan (diambil dari buku kas permanen financial_records agar tidak berkurang jika pesanan dihapus)
     *
     * @return array{total_hari_ini: int, total_pending: int, total_pendapatan: float}
     */
    public function getDashboardStats(): array
    {
        $today = date('Y-m-d');

        $totalHariIni = $this->where('DATE(created_at)', $today)->countAllResults();
        $totalPending = $this->where('status', 'pending')->countAllResults();

        // Total pendapatan HANYA dari buku kas permanen (financial_records)
        // yang sudah terjamin bersih: hanya pesanan berstatus 'selesai' yang pernah direkam
        $financialModel  = new FinancialRecordModel();
        $totalPendapatan = $financialModel->getTotalRevenue();

        // Fallback HANYA jika financial_records benar-benar kosong (sistem baru / belum ada transaksi selesai)
        // Fallback ini pun hanya menghitung pesanan berstatus 'selesai', bukan dibatalkan
        if ($totalPendapatan <= 0) {
            $pendapatanRow = $this->selectSum('total_harga')
                                  ->where('status', 'selesai')
                                  ->first();
            $totalPendapatan = (float) ($pendapatanRow->total_harga ?? 0);
        }

        return [
            'total_hari_ini'   => $totalHariIni,
            'total_pending'    => $totalPending,
            'total_pendapatan' => $totalPendapatan,
        ];
    }

    /**
     * Hapus pesanan yang sudah selesai atau dibatalkan dari daftar aktif dasbor.
     * Pendapatan dan rekap transaksi dipastikan tetap aman tersimpan di financial_records.
     *
     * @param int $orderId
     * @return bool
     */
    public function deleteCompletedOrder(int $orderId): bool
    {
        $order = $this->find($orderId);
        if (! $order) {
            return false;
        }

        // Hanya pesanan selesai atau dibatalkan yang boleh dihapus
        if (! in_array($order->status, ['selesai', 'dibatalkan'], true)) {
            return false;
        }

        // Jika selesai, pastikan data sudah tercatat permanen di financial_records
        if ($order->status === 'selesai') {
            $financialModel = new FinancialRecordModel();
            $financialModel->recordFromOrder($orderId);
        }

        $this->db->transException(true);
        $this->db->transStart();

        try {
            // Hapus relasi order_items
            $this->db->table('order_items')->where('order_id', $orderId)->delete();

            // Hapus relasi order_status_histories
            $this->db->table('order_status_histories')->where('order_id', $orderId)->delete();

            // Hapus pesanan dari tabel orders
            $this->delete($orderId);

            $this->db->transComplete();

            log_message('info', "[OrderModel::deleteCompletedOrder] Pesanan #{$orderId} berhasil dihapus. Rekap finansial tetap tersimpan aman.");
            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', "[OrderModel::deleteCompletedOrder] Gagal menghapus order #{$orderId}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus pesanan berstatus selesai atau dibatalkan secara massal (batch).
     * Seluruh rekap finansial dipastikan tersimpan aman di financial_records.
     *
     * @param array<int> $orderIds
     * @return int Jumlah pesanan yang berhasil dihapus
     */
    public function deleteBatchCompletedOrders(array $orderIds): int
    {
        $orderIds = array_filter(array_map('intval', $orderIds), fn($id) => $id > 0);
        if (empty($orderIds)) {
            return 0;
        }

        $orders = $this->whereIn('id', $orderIds)->findAll();
        $validOrders = array_filter($orders, fn($o) => in_array($o->status, ['selesai', 'dibatalkan'], true));

        if (empty($validOrders)) {
            return 0;
        }

        $financialModel = new FinancialRecordModel();
        $validIds = [];

        foreach ($validOrders as $o) {
            $validIds[] = (int) $o->id;
            // Jika berstatus selesai, pastikan tercatat di buku kas permanen
            if ($o->status === 'selesai') {
                $financialModel->recordFromOrder($o->id);
            }
        }

        $this->db->transException(true);
        $this->db->transStart();

        try {
            $this->db->table('order_items')->whereIn('order_id', $validIds)->delete();
            $this->db->table('order_status_histories')->whereIn('order_id', $validIds)->delete();
            $this->whereIn('id', $validIds)->delete();

            $this->db->transComplete();

            log_message('info', "[OrderModel::deleteBatchCompletedOrders] Sebanyak " . count($validIds) . " pesanan berhasil dihapus massal.");
            return count($validIds);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', "[OrderModel::deleteBatchCompletedOrders] Gagal hapus massal: " . $e->getMessage());
            return 0;
        }
    }
}

