<?php

namespace App\Controllers\Api;

use App\Models\WebhookLogModel;
use App\Libraries\WebhookService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * WebhookWorker Controller (Pseudo-Cron)
 *
 * Di shared hosting, cron job sejati sering kali sulit dikonfigurasi
 * atau sangat dibatasi. Controller ini bertindak sebagai "Pseudo-Cron Worker"
 * yang bisa di-trigger via curl/wget dari cron CPanel, atau layanan external
 * seperti cron-job.org setiap menit.
 */
class WebhookWorker extends ResourceController
{
    protected $format = 'json';

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/webhook/worker
    // Mengeksekusi antrean webhook yang pending atau butuh di-retry.
    // ──────────────────────────────────────────────────────────────────────
    public function processPending(): ResponseInterface
    {
        // Untuk security di production, sebaiknya route ini dilindungi token auth rahasia
        // (Misal: ?token=rahasia123), tapi untuk Fase 5 kita buat terbuka dulu.

        $logModel = new WebhookLogModel();
        $pending  = $logModel->getPendingWebhooks(10); // Batch size 10 agar tidak timeout

        if (empty($pending)) {
            return $this->respond([
                'status'  => 'success',
                'message' => 'Tidak ada webhook yang perlu diproses saat ini.',
                'data'    => ['processed' => 0]
            ], ResponseInterface::HTTP_OK);
        }

        $successCount = 0;
        $failedCount  = 0;

        foreach ($pending as $log) {
            // Coba kirim via WebhookService
            $isSuccess = WebhookService::send($log);

            if ($isSuccess) {
                $logModel->logSuccess((int) $log->id);
                $successCount++;
            } else {
                $logModel->logFailedAttempt((int) $log->id);
                $failedCount++;
            }
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Worker selesai mengeksekusi antrean.',
            'data'    => [
                'processed' => count($pending),
                'success'   => $successCount,
                'failed'    => $failedCount,
            ]
        ], ResponseInterface::HTTP_OK);
    }
}
