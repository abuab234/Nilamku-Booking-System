<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table            = 'order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'order_id',
        'item_id',
        'jumlah',
        'subtotal',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'order_id' => 'required|integer|greater_than[0]',
        'item_id'  => 'required|integer|greater_than[0]',
        'jumlah'   => 'required|integer|greater_than[0]|less_than[256]',
        'subtotal' => 'required|decimal|greater_than_equal_to[0]',
    ];

    /**
     * Ambil semua item dalam satu order, lengkap dengan info item-nya.
     *
     * @param int $orderId
     * @return array<object>
     */
    public function getItemsByOrderId(int $orderId): array
    {
        return $this->db->table('order_items oi')
                        ->select('oi.*, i.nama AS nama_item, i.harga AS harga_satuan, i.foto_url')
                        ->join('items i', 'i.id = oi.item_id')
                        ->where('oi.order_id', $orderId)
                        ->get()
                        ->getResult();
    }

    /**
     * Insert batch order items secara efisien (satu query untuk semua items).
     *
     * @param int   $orderId
     * @param array $cartItems Array of ['item_id', 'jumlah', 'subtotal']
     * @return bool
     */
    public function insertBatchForOrder(int $orderId, array $cartItems): bool
    {
        $data = [];
        $now  = date('Y-m-d H:i:s');

        foreach ($cartItems as $item) {
            $data[] = [
                'order_id'   => $orderId,
                'item_id'    => (int) $item['item_id'],
                'jumlah'     => (int) $item['jumlah'],
                'subtotal'   => (float) $item['subtotal'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $this->db->table($this->table)->insertBatch($data) !== false;
    }
}
