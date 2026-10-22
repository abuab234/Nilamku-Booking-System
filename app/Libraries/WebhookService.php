<?php

namespace App\Libraries;

use App\Models\WebhookLogModel;
use CodeIgniter\HTTP\CURLRequest;

class WebhookService
{
    /**
     * Daftarkan webhook ke tabel webhook_logs untuk dikirim oleh worker.
     * Ini dipanggil dari OrderModel setiap ada perubahan status (sinkron, tapi cepat).
     *
     * @param int $orderId
     * @param string $eventType (misal: 'order.created', 'order.status_changed')
     * @param array $payload
     * @return int|false ID dari log webhook, false jika gagal
     */
    public static function queueNotification(int $orderId, string $eventType, array $payload): int|false
    {
        $logModel = new WebhookLogModel();
        return $logModel->insert([
            'order_id'          => $orderId,
            'event_type'        => $eventType,
            'payload'           => json_encode($payload),
            'status_pengiriman' => 'pending',
            'percobaan_ke'      => 0,
            'next_retry_at'     => date('Y-m-d H:i:s'), // siap dieksekusi sekarang
        ]);
    }

    /**
     * Mengeksekusi pengiriman webhook. Dipanggil oleh Cron Worker.
     *
     * @param object $webhookLog Data dari webhook_logs (per baris)
     * @return bool True jika berhasil, False jika gagal (timeout/HTTP 500/dll)
     */
    public static function send($webhookLog): bool
    {
        // TARGET WEBHOOK URL (bisa dipindah ke .env nanti)
        // Kita gunakan mock URL sementara untuk pengujian
        $webhookUrl = env('WEBHOOK_URL', 'https://postman-echo.com/post');

        try {
            /** @var CURLRequest $client */
            $client = \Config\Services::curlrequest([
                'timeout' => 5, // Set timeout singkat agar cron tidak hang
            ]);

            $response = $client->request('POST', $webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-Event-Type' => $webhookLog->event_type,
                ],
                'body' => $webhookLog->payload,
                'http_errors' => false, // Jangan throw exception jika HTTP 400/500
            ]);

            // Anggap berhasil jika HTTP status 2xx
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            }

            log_message('warning', "[WebhookService::send] Gagal mengirim Webhook ID {$webhookLog->id}. HTTP Status: {$statusCode}");
            return false;

        } catch (\Throwable $e) {
            log_message('error', "[WebhookService::send] Exception Webhook ID {$webhookLog->id}: " . $e->getMessage());
            return false;
        }
    }
}
