<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'timeslot_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            // State machine: Pending → Diproses → Siap → Selesai / Dibatalkan
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'diproses', 'siap', 'selesai', 'dibatalkan'],
                'default'    => 'pending',
            ],
            'total_harga' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'catatan' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('timeslot_id');
        $this->forge->addKey('status');

        // FK: ON DELETE RESTRICT agar order tidak hilang jika user/slot dihapus
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('timeslot_id', 'time_slots', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('orders', true);
    }
}
