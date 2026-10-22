<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryAndPaymentToOrders extends Migration
{
    public function up(): void
    {
        $fields = [
            'nomor_antrian' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'id',
            ],
            'metode_layanan' => [
                'type'       => 'ENUM',
                'constraint' => ['dine_in', 'delivery'],
                'default'    => 'dine_in',
                'after'      => 'catatan',
            ],
            'alamat_pengiriman' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'metode_layanan',
            ],
            'jarak_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'alamat_pengiriman',
            ],
            'biaya_ongkir' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'jarak_km',
            ],
            'metode_pembayaran' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'transfer'],
                'default'    => 'cash',
                'after'      => 'biaya_ongkir',
            ],
            'bukti_transfer' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'metode_pembayaran',
            ],
        ];

        $this->forge->addColumn('orders', $fields);
    }

    public function down(): void
    {
        $columns = [
            'nomor_antrian',
            'metode_layanan',
            'alamat_pengiriman',
            'jarak_km',
            'biaya_ongkir',
            'metode_pembayaran',
            'bukti_transfer',
        ];

        $this->forge->dropColumn('orders', $columns);
    }
}
