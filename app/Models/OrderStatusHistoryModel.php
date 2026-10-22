<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderStatusHistoryModel extends Model
{
    protected $table            = 'order_status_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'order_id',
        'status_lama',
        'status_baru',
        'diubah_oleh',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Rekam perubahan status pesanan ke dalam audit trail.
     * Dipanggil setiap kali status order berubah — termasuk saat order baru dibuat.
     *
     * @param int         $orderId
     * @param string|null $statusLama null jika ini adalah status awal
     * @param string      $statusBaru
     * @param int|null    $diubahOleh user_id yang melakukan perubahan, null jika sistem
     * @param string|null $catatan    keterangan opsional
     * @return int|false  ID history yang baru dibuat, atau false jika gagal
     */
    public function recordTransition(
        int     $orderId,
        ?string $statusLama,
        string  $statusBaru,
        ?int    $diubahOleh = null,
        ?string $catatan    = null
    ): int|false {
        return $this->insert([
            'order_id'    => $orderId,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
            'diubah_oleh' => $diubahOleh,
            'catatan'     => $catatan,
        ]);
    }

    /**
     * Ambil riwayat lengkap perubahan status untuk satu order.
     *
     * @param int $orderId
     * @return array<object>
     */
    public function getHistoryByOrderId(int $orderId): array
    {
        return $this->db->table('order_status_histories h')
                        ->select('h.*, u.nama AS nama_pengubah')
                        ->join('users u', 'u.id = h.diubah_oleh', 'left')
                        ->where('h.order_id', $orderId)
                        ->orderBy('h.created_at', 'ASC')
                        ->get()
                        ->getResult();
    }
}
