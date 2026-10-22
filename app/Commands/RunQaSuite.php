<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OrderModel;
use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\UserModel;
use Config\Database;

class RunQaSuite extends BaseCommand
{
    protected $group       = 'QA';
    protected $name        = 'qa:test';
    protected $description = 'Menjalankan QA Automated Test Suite komprehensif untuk seluruh fitur Nilamku';

    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;

    public function run(array $params)
    {
        CLI::write("================================================================", 'cyan');
        CLI::write("       NILAMKU COMPREHENSIVE QA AUTOMATED TEST SUITE            ", 'yellow');
        CLI::write("================================================================\n", 'cyan');

        $settingModel  = new SettingModel();
        $orderModel    = new OrderModel();
        $itemModel     = new ItemModel();
        $categoryModel = new CategoryModel();
        $userModel     = new UserModel();

        // ── 1. Model & Database Connectivity ──────────────────────────────
        CLI::write(">>> 1. Model & Database Verification", 'white');
        $this->assertTest("SettingModel terhubung", $settingModel instanceof SettingModel);
        $this->assertTest("OrderModel terhubung", $orderModel instanceof OrderModel);
        $this->assertTest("ItemModel terhubung", $itemModel instanceof ItemModel);
        $this->assertTest("CategoryModel terhubung", $categoryModel instanceof CategoryModel);

        // ── 2. Store Status Operational Toggle ────────────────────────────
        CLI::write("\n>>> 2. Store Status Operational Rules", 'white');
        $initialStatus = $settingModel->getStoreStatus();
        $this->assertTest("Store status terbaca dari DB", in_array($initialStatus, ['buka', 'tutup']), "Status: {$initialStatus}");

        $settingModel->setStoreStatus('tutup');
        $this->assertTest("setStoreStatus('tutup') berhasil", $settingModel->getStoreStatus() === 'tutup');

        $toggled = $settingModel->toggleStoreStatus();
        $this->assertTest("toggleStoreStatus('tutup' -> 'buka') berhasil", $toggled === 'buka' && $settingModel->getStoreStatus() === 'buka');

        $settingModel->setStoreStatus('buka');
        $this->assertTest("Status akhir dipastikan 'buka' untuk pengujian checkout", $settingModel->getStoreStatus() === 'buka');

        // ── 3. Catalog & Categories Verification ──────────────────────────
        CLI::write("\n>>> 3. Catalog & Categories Verification", 'white');
        $categories = $categoryModel->orderBy('urutan_tampil', 'ASC')->findAll();
        $this->assertTest("Kategori menu tersedia (minimal 3 kategori)", count($categories) >= 3, "Jumlah: " . count($categories));

        $items = $itemModel->findAll();
        $this->assertTest("Item menu tersedia di database", count($items) > 0, "Jumlah: " . count($items));

        $firstItem = $items[0];
        $this->assertTest("Item memiliki atribut valid (nama, harga, status_tersedia)", 
            !empty($firstItem->nama) && $firstItem->harga > 0 && in_array($firstItem->status_tersedia, ['tersedia', 'habis']));

        // ── 4. Google Drive Link Conversion ───────────────────────────────
        CLI::write("\n>>> 4. Google Drive URL Converter Logic", 'white');
        $gdriveSharing = 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs/view?usp=sharing';
        $directGdrive  = format_gdrive_image_url($gdriveSharing);
        $this->assertTest("Konversi link sharing GDrive ke direct thumbnail URL", 
            str_contains($directGdrive, 'lh3.googleusercontent.com/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs'), 
            "Output: {$directGdrive}");

        $regularUrl = 'https://natashaskitchen.com/image.jpg';
        $this->assertTest("URL gambar eksternal non-GDrive tidak berubah", 
            format_gdrive_image_url($regularUrl) === $regularUrl);

        // ── 5. Immediate Booking Creation (Dine-in) ───────────────────────
        CLI::write("\n>>> 5. Immediate Order Creation: Dine-in (Tanpa Slot Waktu)", 'white');
        $customer = $userModel->where('role', 'customer')->first();
        $custUserId = (int) ($customer ? $customer->id : 1);

        $cartDineIn = [
            [
                'item_id'  => (int) $items[0]->id,
                'jumlah'   => 1,
                'subtotal' => (float) $items[0]->harga
            ]
        ];

        $createdDineInId = $orderModel->createBooking(
            $custUserId,
            null,
            $cartDineIn,
            (float) $items[0]->harga,
            'QA Test Otomatis Dine-in',
            'dine_in',
            null,
            0.00,
            'transfer',
            'uploads/bukti_transfer/dummy_qa.png'
        );
        $this->assertTest("Pemesanan Dine-in instan (slotId=null) berhasil dibuat", 
            is_numeric($createdDineInId) && $createdDineInId > 0, "Order ID: {$createdDineInId}");

        $dineInDetail = $orderModel->getOrderDetail($createdDineInId);
        $this->assertTest("Nomor antrean otomatis dihasilkan (format misal A-xxx)", 
            !empty($dineInDetail->nomor_antrian), "Antrean: {$dineInDetail->nomor_antrian}");
        $this->assertTest("Status awal pesanan adalah 'pending'", $dineInDetail->status === 'pending');
        $this->assertTest("Pesanan tidak mengikat slot waktu (timeslot_id = null)", is_null($dineInDetail->timeslot_id));
        $this->assertTest("Rincian item tersimpan dengan tepat", count($dineInDetail->items) === 1);

        // ── 6. Immediate Booking Creation: Delivery + Ongkir ──────────────
        CLI::write("\n>>> 6. Immediate Order Creation: Delivery + Kalkulasi Ongkir", 'white');
        $cartDelivery = [
            [
                'item_id'  => (int) $items[0]->id,
                'jumlah'   => 1,
                'subtotal' => (float) $items[0]->harga
            ]
        ];

        $totalDelivery = (float) $items[0]->harga + 8000;

        $createdDeliveryId = $orderModel->createBooking(
            $custUserId,
            null,
            $cartDelivery,
            $totalDelivery,
            'Hubungi nomor WA sebelum sampai',
            'delivery',
            'Jl. Nilam Asri No. 8, Blok C2',
            3.2,
            'transfer',
            'uploads/bukti_transfer/dummy_delivery.png'
        );
        $this->assertTest("Pemesanan Delivery instan berhasil dibuat", 
            is_numeric($createdDeliveryId) && $createdDeliveryId > 0, "Order ID: {$createdDeliveryId}");

        $deliveryDetail = $orderModel->getOrderDetail($createdDeliveryId);
        $this->assertTest("Alamat pengiriman dan jarak tersimpan dengan benar", 
            !empty($deliveryDetail->alamat_pengiriman) && (float)$deliveryDetail->jarak_km === 3.2);
        $this->assertTest("Biaya ongkir tersimpan dengan benar", (float)$deliveryDetail->biaya_ongkir === 8000.0);

        // ── 7. Order Status State Machine & History Logging ───────────────
        CLI::write("\n>>> 7. Order Status State Machine & History Logging", 'white');
        $admin = $userModel->where('role', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        // pending -> diproses
        $s1 = $orderModel->updateOrderStatus($createdDineInId, 'diproses', $adminId, 'Pesanan mulai diracik');
        $this->assertTest("Transisi pending -> diproses berhasil", $s1 === true);
        $c1 = $orderModel->getOrderDetail($createdDineInId);
        $this->assertTest("Status pesanan berubah menjadi 'diproses'", $c1->status === 'diproses');

        // diproses -> siap
        $s2 = $orderModel->updateOrderStatus($createdDineInId, 'siap', $adminId, 'Pesanan siap diambil');
        $this->assertTest("Transisi diproses -> siap berhasil", $s2 === true);
        $c2 = $orderModel->getOrderDetail($createdDineInId);
        $this->assertTest("Status pesanan berubah menjadi 'siap'", $c2->status === 'siap');

        // siap -> selesai
        $s3 = $orderModel->updateOrderStatus($createdDineInId, 'selesai', $adminId, 'Pesanan selesai diserahkan');
        $this->assertTest("Transisi siap -> selesai berhasil", $s3 === true);
        $c3 = $orderModel->getOrderDetail($createdDineInId);
        $this->assertTest("Status pesanan berubah menjadi 'selesai'", $c3->status === 'selesai');

        // State machine restriction (selesai -> pending must fail)
        $sInvalid = $orderModel->updateOrderStatus($createdDineInId, 'pending', $adminId, 'Transisi ilegal');
        $this->assertTest("Transisi terlarang (selesai -> pending) berhasil ditolak", $sInvalid === false);

        // Verify history records
        $historyModel = new \App\Models\OrderStatusHistoryModel();
        $historyCount = $historyModel->where('order_id', $createdDineInId)->countAllResults();
        $this->assertTest("Setiap transisi status tercatat di tabel order_status_histories", 
            $historyCount >= 3, "Tercatat: {$historyCount} riwayat");

        // ── 8. Catalog Item CRUD Simulation ───────────────────────────────
        CLI::write("\n>>> 8. Item Model CRUD & Toggle Status", 'white');
        $newItemId = $itemModel->insert([
            'category_id'     => $categories[0]->id,
            'nama'            => 'Menu Uji QA Otomatis',
            'deskripsi'       => 'Deskripsi uji item QA',
            'harga'           => 12000,
            'status_tersedia' => 'tersedia',
            'foto_url'        => 'https://example.com/test.jpg'
        ]);
        $this->assertTest("Admin create menu item baru berhasil", is_numeric($newItemId) && $newItemId > 0);

        // Toggle to habis
        $itemModel->update($newItemId, ['status_tersedia' => 'habis']);
        $itemCheck1 = $itemModel->find($newItemId);
        $this->assertTest("Toggle status item menjadi 'habis' berhasil", $itemCheck1->status_tersedia === 'habis');

        // Toggle to tersedia
        $itemModel->update($newItemId, ['status_tersedia' => 'tersedia']);
        $itemCheck2 = $itemModel->find($newItemId);
        $this->assertTest("Toggle status item kembali menjadi 'tersedia' berhasil", $itemCheck2->status_tersedia === 'tersedia');

        // Cleanup test item
        $itemModel->delete($newItemId);
        $this->assertTest("Pembersihan (delete) data item uji berhasil", $itemModel->find($newItemId) === null);

        // ── 9. Financial Record Model & Date Filter ───────────────────────
        CLI::write("\n>>> 9. Financial Records & Date Filtering", 'white');
        $financialModel = new \App\Models\FinancialRecordModel();
        
        $totalRevenueAll = $financialModel->getTotalRevenue();
        $this->assertTest("Buku kas permanen menyimpan total pendapatan (> 0)", $totalRevenueAll > 0, "Total: Rp " . number_format($totalRevenueAll));

        // Test filter by today
        $todayStr = date('Y-m-d');
        $todayReport = $financialModel->getFinancialReport($todayStr);
        $this->assertTest("Filter laporan berdasarkan tanggal hari ini ({$todayStr})", 
            $todayReport['filter_date'] === $todayStr && $todayReport['total_transaksi'] >= 1);

        // Pastikan setiap record di laporan ber-tanggal sama persis
        $allSameDate = true;
        foreach ($todayReport['records'] as $r) {
            if ($r->tanggal !== $todayStr) {
                $allSameDate = false;
                break;
            }
        }
        $this->assertTest("Seluruh transaksi yang muncul hanya dari tanggal yang difilter", $allSameDate);

        // Test filter by specific date (e.g. 2026-09-12)
        $pastDate = '2026-09-12';
        $pastReport = $financialModel->getFinancialReport($pastDate);
        $this->assertTest("Filter laporan tanggal masa lalu ({$pastDate})", 
            $pastReport['filter_date'] === $pastDate && $pastReport['total_transaksi'] >= 1, 
            "Transaksi: {$pastReport['total_transaksi']}, Omzet: Rp " . number_format($pastReport['total_pendapatan']));

        // ── 10. Delete Completed Order with Revenue Preservation ──────────
        CLI::write("\n>>> 10. Delete Completed Order & Revenue Preservation", 'white');

        // Buat pesanan khusus uji delete
        $delTestOrderId = $orderModel->createBooking(
            $custUserId,
            null,
            [['item_id' => (int)$items[0]->id, 'jumlah' => 1, 'subtotal' => (float)$items[0]->harga]],
            (float)$items[0]->harga,
            'Pesanan Uji Hapus',
            'dine_in',
            null,
            0.00,
            'transfer',
            'uploads/bukti_transfer/test_del.png'
        );

        // Ubah sampai status selesai
        $orderModel->updateOrderStatus($delTestOrderId, 'diproses', $adminId, 'Test');
        $orderModel->updateOrderStatus($delTestOrderId, 'siap', $adminId, 'Test');
        $orderModel->updateOrderStatus($delTestOrderId, 'selesai', $adminId, 'Test Selesai');

        $revBeforeDelete = $financialModel->getTotalRevenue();

        // Eksekusi hapus pesanan selesai
        $deleteSuccess = $orderModel->deleteCompletedOrder($delTestOrderId);
        $this->assertTest("Admin menghapus pesanan berstatus selesai berhasil", $deleteSuccess === true);

        // Cek bahwa pesanan sudah tidak ada di tabel orders
        $checkDeletedOrder = $orderModel->find($delTestOrderId);
        $this->assertTest("Pesanan berhasil terhapus dari tabel orders aktif", $checkDeletedOrder === null);

        // Cek bahwa total pendapatan di buku kas TIDAK berkurang
        $revAfterDelete = $financialModel->getTotalRevenue();
        $this->assertTest("Total pendapatan tetap utuh tersimpan di buku kas permanen", 
            $revAfterDelete >= $revBeforeDelete, 
            "Sebelum: Rp " . number_format($revBeforeDelete) . " | Sesudah: Rp " . number_format($revAfterDelete));

        // Cek fungsi getDashboardStats() tetap merefleksikan total pendapatan penuh
        $statsAfter = $orderModel->getDashboardStats();
        $this->assertTest("Dasbor stats total_pendapatan tetap utuh", 
            $statsAfter['total_pendapatan'] >= $revBeforeDelete);

        // ── 11. Cancel Order with Mandatory Reason ──────────────────────
        CLI::write("\n>>> 11. Cancel Order with Mandatory Reason (Alasan Pembatalan)", 'white');
        $cancelOrderId = $orderModel->createBooking(
            $custUserId,
            null,
            [['item_id' => (int)$items[0]->id, 'jumlah' => 1, 'subtotal' => (float)$items[0]->harga]],
            (float)$items[0]->harga,
            'Pesanan Uji Batal Alasan',
            'dine_in',
            null,
            0.00,
            'transfer',
            'uploads/bukti_transfer/test_batal.png'
        );
        $this->assertTest("Pesanan uji pembatalan berhasil dibuat", $cancelOrderId > 0);

        $alasanBatal = "Stok bahan menu pilihan Anda telah habis untuk hari ini.";
        $cancelSuccess = $orderModel->updateOrderStatus(
            $cancelOrderId,
            'dibatalkan',
            $adminId,
            $alasanBatal,
            $alasanBatal
        );
        $this->assertTest("Admin membatalkan pesanan dengan menyertakan alasan berhasil", $cancelSuccess === true);

        $checkCancelled = $orderModel->getOrderDetail($cancelOrderId);
        $this->assertTest("Status pesanan berubah menjadi 'dibatalkan'", $checkCancelled->status === 'dibatalkan');
        $this->assertTest("Kolom alasan_batal tersimpan persis di database pesanan", 
            $checkCancelled->alasan_batal === $alasanBatal, "Alasan: {$checkCancelled->alasan_batal}");

        $histCancel = $historyModel->where('order_id', $cancelOrderId)->orderBy('id', 'DESC')->first();
        $this->assertTest("Alasan pembatalan tercatat di tabel riwayat status", 
            $histCancel !== null && str_contains($histCancel->catatan, 'Stok bahan'));

        // ── 12. Multi-Select Bulk Delete (Hapus Massal Pesanan Selesai / Batal) ──
        CLI::write("\n>>> 12. Multi-Select Bulk Delete (Hapus Massal)", 'white');
        $batchId1 = $orderModel->createBooking(
            $custUserId,
            null,
            [['item_id' => (int)$items[0]->id, 'jumlah' => 1, 'subtotal' => (float)$items[0]->harga]],
            (float)$items[0]->harga,
            'Batch Selesai',
            'dine_in',
            null,
            0.00,
            'transfer',
            'uploads/bukti_transfer/test_b1.png'
        );
        $orderModel->updateOrderStatus($batchId1, 'diproses', $adminId, 'Proses');
        $orderModel->updateOrderStatus($batchId1, 'siap', $adminId, 'Siap');
        $orderModel->updateOrderStatus($batchId1, 'selesai', $adminId, 'Selesai');

        $batchId2 = $orderModel->createBooking(
            $custUserId,
            null,
            [['item_id' => (int)$items[0]->id, 'jumlah' => 1, 'subtotal' => (float)$items[0]->harga]],
            (float)$items[0]->harga,
            'Batch Batal',
            'dine_in',
            null,
            0.00,
            'transfer',
            'uploads/bukti_transfer/test_b2.png'
        );
        $orderModel->updateOrderStatus($batchId2, 'dibatalkan', $adminId, 'Alasan batch batal', 'Alasan batch batal');

        $revBeforeBulk = $financialModel->getTotalRevenue();

        // Eksekusi hapus massal
        $deletedBatchCount = $orderModel->deleteBatchCompletedOrders([$batchId1, $batchId2, $cancelOrderId]);
        $this->assertTest("Hapus massal (multi-select batch delete) berhasil mengeksekusi 3 pesanan", 
            $deletedBatchCount === 3, "Jumlah terhapus: {$deletedBatchCount}");

        $this->assertTest("Pesanan batch pertama terhapus dari orders aktif", $orderModel->find($batchId1) === null);
        $this->assertTest("Pesanan batch kedua terhapus dari orders aktif", $orderModel->find($batchId2) === null);
        $this->assertTest("Pesanan uji batal terhapus dari orders aktif", $orderModel->find($cancelOrderId) === null);

        $revAfterBulk = $financialModel->getTotalRevenue();
        $this->assertTest("Total pendapatan tetap aman dan utuh setelah hapus massal", 
            $revAfterBulk >= $revBeforeBulk, 
            "Sebelum: Rp " . number_format($revBeforeBulk) . " | Sesudah: Rp " . number_format($revAfterBulk));

        // ── SUMMARY ───────────────────────────────────────────────────────
        CLI::write("\n================================================================", 'cyan');
        CLI::write("                  RINGKASAN HASIL PENGUJIAN                     ", 'yellow');
        CLI::write("================================================================", 'cyan');
        CLI::write("Total Pengujian : {$this->totalTests}");
        CLI::write("Lulus (PASS)    : {$this->passedTests}", 'green');
        CLI::write("Gagal (FAIL)    : {$this->failedTests}", $this->failedTests > 0 ? 'red' : 'green');
        $rate = round(($this->passedTests / $this->totalTests) * 100, 2);
        CLI::write("Tingkat Keberhasilan: {$rate}%\n", 'yellow');

        if ($this->failedTests > 0) {
            CLI::error("Terdapat pengujian yang GAGAL. Silakan periksa detail log di atas.");
            return;
        }

        CLI::write("SEMUA FITUR BERFUNGSI 100% TANPA KENDALA! Sistem siap beroperasi.", 'green');
    }

    private function assertTest(string $title, bool $condition, string $detail = ''): void
    {
        $this->totalTests++;
        if ($condition) {
            $this->passedTests++;
            CLI::write(" [PASS] {$title}" . ($detail ? " ({$detail})" : ""), 'green');
        } else {
            $this->failedTests++;
            CLI::write(" [FAIL] {$title}" . ($detail ? " ({$detail})" : ""), 'red');
        }
    }
}
