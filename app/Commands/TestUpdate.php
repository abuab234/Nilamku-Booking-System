<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OrderModel;
use App\Models\WebhookLogModel;

class TestUpdate extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:update';
    protected $description = 'Script sederhana untuk menguji updateOrderStatus dan Webhook Queue';

    public function run(array $params)
    {
        CLI::write('=== TEST: Update Order Status & Webhook Trigger ===', 'yellow');

        $orderModel = new OrderModel();
        
        // Asumsi Order ID 1 ada dan statusnya pending (dari Fase 4)
        $orderId = 1;
        $statusBaru = 'diproses';
        $adminId = 1;

        CLI::write("Mengubah pesanan #{$orderId} menjadi '{$statusBaru}'...");
        
        $success = $orderModel->updateOrderStatus($orderId, $statusBaru, $adminId, 'Diproses via test script');
        
        if ($success) {
            CLI::write('✅ Update status BERHASIL.', 'green');
        } else {
            CLI::write('❌ Update status GAGAL.', 'red');
            return;
        }

        // Cek tabel webhook_logs
        CLI::write("\n=== Mengecek isi tabel webhook_logs ===", 'yellow');
        $webhookModel = new WebhookLogModel();
        $logs = $webhookModel->findAll();

        if (empty($logs)) {
            CLI::write('Tabel webhook_logs kosong!', 'red');
            return;
        }

        foreach ($logs as $log) {
            CLI::write("ID: {$log->id}");
            CLI::write("Event: {$log->event_type}");
            CLI::write("Status Pengiriman: {$log->status_pengiriman}");
            CLI::write("Payload: " . wordwrap($log->payload, 80, "\n  "));
            CLI::write("-------------------------------------------------");
        }
    }
}
