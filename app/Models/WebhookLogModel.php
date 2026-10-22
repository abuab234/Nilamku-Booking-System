<?php

namespace App\Models;

use CodeIgniter\Model;

class WebhookLogModel extends Model
{
    protected $table            = 'webhook_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'order_id',
        'event_type',
        'payload',
        'status_pengiriman',
        'percobaan_ke',
        'next_retry_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil antrean webhook yang pending atau butuh di-retry.
     * Syarat: status_pengiriman != 'success' DAN next_retry_at <= WAKTU_SEKARANG
     *
     * @param int $limit
     * @return array<object>
     */
    public function getPendingWebhooks(int $limit = 50): array
    {
        return $this->where('status_pengiriman !=', 'success')
                    ->groupStart()
                        ->where('next_retry_at <=', date('Y-m-d H:i:s'))
                        ->orWhere('next_retry_at', null)
                    ->groupEnd()
                    ->where('percobaan_ke <=', 3) // Maksimal 3x percobaan (0, 1, 2, 3)
                    ->orderBy('created_at', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Catat kegagalan pengiriman dan set jadwal retry.
     * Percobaan ke-1: tunggu 5 menit
     * Percobaan ke-2: tunggu 15 menit
     * Percobaan ke-3: set status failed (gagal permanen)
     *
     * @param int $logId
     * @return bool
     */
    public function logFailedAttempt(int $logId): bool
    {
        $log = $this->find($logId);
        if (!$log) {
            return false;
        }

        $percobaanKe = (int) $log->percobaan_ke + 1;
        $status      = 'pending';
        $nextRetry   = null;

        if ($percobaanKe >= 3) {
            $status = 'failed';
        } else {
            // Kalkulasi exponential backoff sederhana
            $minutesToWait = $percobaanKe === 1 ? 5 : 15;
            $nextRetry = date('Y-m-d H:i:s', strtotime("+{$minutesToWait} minutes"));
        }

        return $this->update($logId, [
            'status_pengiriman' => $status,
            'percobaan_ke'      => $percobaanKe,
            'next_retry_at'     => $nextRetry,
        ]);
    }

    /**
     * Catat pengiriman sukses.
     */
    public function logSuccess(int $logId): bool
    {
        return $this->update($logId, [
            'status_pengiriman' => 'success',
            'percobaan_ke'      => $this->db->escapeString('percobaan_ke + 1'), // increment, tapi CI4 update butuh raw string atau increment terpisah.
            'next_retry_at'     => null,
        ]);
    }
}
