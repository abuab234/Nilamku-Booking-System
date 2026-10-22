<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlasanBatalToOrders extends Migration
{
    public function up()
    {
        $fields = [
            'alasan_batal' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'catatan',
            ],
        ];

        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'alasan_batal');
    }
}
