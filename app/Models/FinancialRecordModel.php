<?php

namespace App\Models;

use CodeIgniter\Model;

class FinancialRecordModel extends Model
{
    protected $table            = 'financial_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'order_id',
        'nomor_antrian',
        'nama_pelanggan',
        'no_wa',
        'tanggal',
        'waktu',
        'metode_layanan',
        'metode_pembayaran',
        'subtotal_menu',
        'biaya_ongkir',
        'total_pendapatan',
        'rincian_item',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Rekam atau sinkronkan data keuangan dari pesanan ke buku kas permanen.
     *
     * @param object|int $order ID pesanan atau object pesanan
     * @return bool
     */
    public function recordFromOrder($order): bool
    {
        $db = \Config\Database::connect();

        $orderId = is_object($order) ? (int) $order->id : (int) $order;

        $orderRow = $db->table('orders')
                       ->select('orders.*, users.nama as user_nama, users.no_telepon as user_phone')
                       ->join('users', 'users.id = orders.user_id', 'left')
                       ->where('orders.id', $orderId)
                       ->get()
                       ->getFirstRow();

        if (! $orderRow) {
            return false;
        }

        // Jangan rekam pesanan yang dibatalkan
        if ($orderRow->status === 'dibatalkan') {
            return false;
        }

        // Ambil rincian item
        $items = $db->table('order_items')
                    ->select('order_items.*, items.nama as nama_item, items.harga as harga_item')
                    ->join('items', 'items.id = order_items.item_id', 'left')
                    ->where('order_id', $orderId)
                    ->get()
                    ->getResult();

        $subtotalMenu = 0.00;
        $itemsSummary = [];
        foreach ($items as $it) {
            $subtotalMenu += (float) $it->subtotal;
            $hargaSatuan   = isset($it->harga_item) ? (float) $it->harga_item : ((int) $it->jumlah > 0 ? ((float) $it->subtotal / (int) $it->jumlah) : 0);
            $itemsSummary[] = [
                'nama'     => $it->nama_item ?? "Item #{$it->item_id}",
                'jumlah'   => (int) $it->jumlah,
                'harga'    => $hargaSatuan,
                'subtotal' => (float) $it->subtotal,
            ];
        }

        $ongkir = (float) ($orderRow->biaya_ongkir ?? 0.00);
        $total  = (float) ($orderRow->total_harga ?? ($subtotalMenu + $ongkir));
        $tgl    = date('Y-m-d', strtotime($orderRow->created_at ?? 'now'));
        $wkt    = date('H:i:s', strtotime($orderRow->created_at ?? 'now'));

        $nama = ! empty($orderRow->user_nama) ? $orderRow->user_nama : 'Pelanggan';
        $wa   = ! empty($orderRow->user_phone) ? $orderRow->user_phone : '-';

        $data = [
            'order_id'          => $orderId,
            'nomor_antrian'     => $orderRow->nomor_antrian ?? 'ORD-' . $orderId,
            'nama_pelanggan'    => $nama,
            'no_wa'             => $wa,
            'tanggal'           => $tgl,
            'waktu'             => $wkt,
            'metode_layanan'    => $orderRow->metode_layanan ?? 'dine_in',
            'metode_pembayaran' => $orderRow->metode_pembayaran ?? 'transfer',
            'subtotal_menu'     => $subtotalMenu,
            'biaya_ongkir'      => $ongkir,
            'total_pendapatan'  => $total,
            'rincian_item'      => json_encode($itemsSummary, JSON_UNESCAPED_UNICODE),
            'catatan'           => $orderRow->catatan,
        ];

        // Cek apakah sudah pernah tercatat
        $existing = $this->where('order_id', $orderId)->first();
        if ($existing) {
            return (bool) $this->update($existing->id, $data);
        }

        return (bool) $this->insert($data);
    }

    /**
     * Hitung total akumulasi seluruh pendapatan yang pernah tercatat di buku kas.
     *
     * @return float
     */
    public function getTotalRevenue(): float
    {
        $row = $this->selectSum('total_pendapatan', 'total')->first();
        return (float) ($row->total ?? 0.00);
    }

    /**
     * Hitung total pendapatan untuk tanggal tertentu.
     *
     * @param string $date format 'YYYY-MM-DD'
     * @return float
     */
    public function getDailyRevenue(string $date): float
    {
        $row = $this->selectSum('total_pendapatan', 'total')
                    ->where('tanggal', $date)
                    ->first();
        return (float) ($row->total ?? 0.00);
    }

    /**
     * Ambil data laporan keuangan dengan filter tanggal (opsional).
     *
     * @param string|null $date format 'YYYY-MM-DD' atau null untuk semua tanggal
     * @return array{
     *     records: array,
     *     total_pendapatan: float,
     *     total_transaksi: int,
     *     total_menu: float,
     *     total_ongkir: float,
     *     filter_date: string|null
     * }
     */
    public function getFinancialReport(?string $date = null): array
    {
        $builder = $this->orderBy('tanggal', 'DESC')
                        ->orderBy('waktu', 'DESC');

        if (! empty($date)) {
            $builder->where('tanggal', $date);
        }

        $records = $builder->findAll();

        $totalPendapatan = 0.00;
        $totalMenu       = 0.00;
        $totalOngkir     = 0.00;

        foreach ($records as $rec) {
            $totalPendapatan += (float) $rec->total_pendapatan;
            $totalMenu       += (float) $rec->subtotal_menu;
            $totalOngkir     += (float) $rec->biaya_ongkir;

            // Decode rincian item jika dalam format JSON
            if (! empty($rec->rincian_item)) {
                $decoded = json_decode($rec->rincian_item, true);
                $rec->items_decoded = is_array($decoded) ? $decoded : [];
            } else {
                $rec->items_decoded = [];
            }
        }

        return [
            'records'          => $records,
            'total_pendapatan' => $totalPendapatan,
            'total_transaksi'  => count($records),
            'total_menu'       => $totalMenu,
            'total_ongkir'     => $totalOngkir,
            'filter_date'      => $date,
        ];
    }

    /**
     * Hapus rekaman finansial berdasarkan order_id.
     * Digunakan sebagai safeguard jika ada pesanan yang sempat tercatat sebagai 'selesai'
     * lalu status-nya berubah menjadi 'dibatalkan' — agar tidak masuk hitungan pendapatan.
     *
     * @param int $orderId
     * @return bool
     */
    public function removeByOrderId(int $orderId): bool
    {
        $existing = $this->where('order_id', $orderId)->first();
        if (! $existing) {
            return true; // Tidak ada yang perlu dihapus
        }
        return (bool) $this->delete($existing->id);
    }
}
