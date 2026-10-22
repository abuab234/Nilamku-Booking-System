<?php

namespace App\Controllers\Admin;

use App\Models\CategoryModel;
use App\Models\FinancialRecordModel;
use App\Models\ItemModel;
use App\Models\OrderModel;
use App\Models\SettingModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    protected OrderModel $orderModel;
    protected ItemModel $itemModel;
    protected CategoryModel $categoryModel;
    protected SettingModel $settingModel;
    protected FinancialRecordModel $financialRecordModel;

    public function __construct()
    {
        $this->orderModel           = new OrderModel();
        $this->itemModel            = new ItemModel();
        $this->categoryModel        = new CategoryModel();
        $this->settingModel         = new SettingModel();
        $this->financialRecordModel = new FinancialRecordModel();
    }

    /**
     * Tampilan utama dasbor admin:
     * - Statistik metrik
     * - Status operasional toko (buka/tutup)
     * - Daftar pesanan aktif beserta relasinya
     * - Daftar item katalog & kategori
     * - Laporan keuangan (bisa filter per tanggal)
     */
    public function index()
    {
        $orders      = $this->orderModel->getOrdersForDashboard();
        $stats       = $this->orderModel->getDashboardStats();
        $items       = $this->itemModel->getAllItemsWithCategory();
        $categories  = $this->categoryModel->getActiveCategories();
        $storeStatus = $this->settingModel->getStoreStatus();

        // Filter tanggal untuk Laporan Keuangan
        $rawFilter = trim((string) $this->request->getGet('filter_date'));
        $filterDate = (! empty($rawFilter) && $rawFilter !== 'all') ? $rawFilter : null;

        $financialReport = $this->financialRecordModel->getFinancialReport($filterDate);

        $data = [
            'title'            => 'Dasbor Admin - Nilamku Booking',
            'orders'           => $orders,
            'stats'            => $stats,
            'items'            => $items,
            'categories'       => $categories,
            'store_status'     => $storeStatus,
            'financial_report' => $financialReport,
            'filter_date'      => $filterDate,
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Endpoint update status pesanan (state machine enforcement).
     * POST /admin/orders/update-status
     */
    public function updateStatus()
    {
        $orderId     = (int) $this->request->getPost('order_id');
        $newStatus   = (string) $this->request->getPost('new_status');
        $catatan     = (string) ($this->request->getPost('catatan') ?? '');
        $alasanBatal = trim((string) ($this->request->getPost('alasan_batal') ?? ''));

        $allowedStatuses = ['pending', 'diproses', 'siap', 'selesai', 'dibatalkan'];

        if (! $orderId || ! in_array($newStatus, $allowedStatuses, true)) {
            return redirect()->to('/admin/dashboard')
                             ->with('error', 'Permintaan tidak valid: ID pesanan atau status tujuan tidak sesuai.');
        }

        // Jika status dibatalkan, alasan wajib diisi
        if ($newStatus === 'dibatalkan' && empty($alasanBatal)) {
            return redirect()->to('/admin/dashboard')
                             ->with('error', "Pembatalan gagal: Alasan pembatalan pesanan #{$orderId} wajib diisi agar pelanggan mengetahui alasannya.");
        }

        $userId = (int) (session()->get('id') ?? session()->get('user_id') ?? 1);

        $success = $this->orderModel->updateOrderStatus($orderId, $newStatus, $userId, $catatan, $alasanBatal ?: null);

        if (! $success) {
            return redirect()->to('/admin/dashboard')
                             ->with('error', "Gagal memperbarui pesanan #{$orderId}. Transisi status tidak valid atau pesanan tidak ditemukan.");
        }

        $msg = ($newStatus === 'dibatalkan')
            ? "Pesanan #{$orderId} berhasil dibatalkan dengan alasan: \"{$alasanBatal}\"."
            : "Status pesanan #{$orderId} berhasil diubah menjadi: {$newStatus}.";

        return redirect()->to('/admin/dashboard')
                         ->with('success', $msg);
    }

    /**
     * Endpoint toggle status operasional toko (buka / tutup).
     * POST /admin/store-status
     */
    public function toggleStoreStatus()
    {
        $targetStatus = $this->request->getPost('status');

        if ($targetStatus && in_array(strtolower($targetStatus), ['buka', 'tutup'], true)) {
            $newStatus = strtolower($targetStatus);
            $this->settingModel->setStoreStatus($newStatus);
        } else {
            $newStatus = $this->settingModel->toggleStoreStatus();
        }

        $label = ($newStatus === 'buka') ? 'BUKA (Siap Melayani Pelanggan)' : 'TUTUP (Tidak Beroperasi)';

        return redirect()->to('/admin/dashboard')
                         ->with('success', "Status operasional toko berhasil diubah menjadi: <strong>{$label}</strong>.");
    }

    /**
     * Endpoint hapus pesanan selesai atau dibatalkan dari daftar aktif.
     * Pendapatan dan rekap transaksi tetap tersimpan aman di financial_records.
     * POST /admin/orders/delete
     */
    public function deleteOrder()
    {
        $orderId = (int) $this->request->getPost('order_id');

        if (! $orderId) {
            return redirect()->to('/admin/dashboard')
                             ->with('error', 'Permintaan tidak valid: ID pesanan tidak ditemukan.');
        }

        $success = $this->orderModel->deleteCompletedOrder($orderId);

        if (! $success) {
            return redirect()->to('/admin/dashboard')
                             ->with('error', "Gagal menghapus pesanan #{$orderId}. Hanya pesanan berstatus 'selesai' atau 'dibatalkan' yang dapat dihapus.");
        }

        return redirect()->to('/admin/dashboard')
                         ->with('success', "Pesanan #{$orderId} berhasil dihapus dari antrean aktif. Rekap transaksi tetap tersimpan aman di Laporan Keuangan (hanya pesanan selesai yang masuk pembukuan pendapatan).");
    }

    /**
     * Endpoint hapus pesanan selesai atau dibatalkan secara massal (multi-select).
     * POST /admin/orders/delete-batch
     */
    public function deleteBatchOrders()
    {
        $rawIds = $this->request->getPost('order_ids');
        if (! is_array($rawIds) || empty($rawIds)) {
            return redirect()->to('/admin/dashboard#orders')
                             ->with('error', 'Pilih minimal satu pesanan selesai atau dibatalkan yang ingin dihapus.');
        }

        $orderIds = array_map('intval', $rawIds);
        $deletedCount = $this->orderModel->deleteBatchCompletedOrders($orderIds);

        if ($deletedCount <= 0) {
            return redirect()->to('/admin/dashboard#orders')
                             ->with('error', 'Tidak ada pesanan selesai atau dibatalkan yang dapat dihapus dari pilihan Anda.');
        }

        return redirect()->to('/admin/dashboard#orders')
                         ->with('success', "Berhasil menghapus {$deletedCount} pesanan dari antrean aktif. Rekap transaksi tetap tersimpan aman di Laporan Keuangan (hanya pesanan selesai yang masuk pembukuan pendapatan).");
    }
}
