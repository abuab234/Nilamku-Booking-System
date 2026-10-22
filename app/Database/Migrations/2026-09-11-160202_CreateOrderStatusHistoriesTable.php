<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderStatusHistoriesTable extends Migration
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
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            // NULL jika ini adalah status awal saat order baru dibuat
            'status_lama' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'diproses', 'siap', 'selesai', 'dibatalkan'],
                'null'       => true,
                'default'    => null,
            ],
            'status_baru' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'diproses', 'siap', 'selesai', 'dibatalkan'],
            ],
            // NULL jika perubahan dilakukan oleh system/otomatis
            'diubah_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'catatan' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('order_id');
        $this->forge->addKey('diubah_oleh');

        // CASCADE DELETE: history ikut terhapus jika order dihapus
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        // SET NULL: history tetap ada meski user yang mengubah dihapus
        $this->forge->addForeignKey('diubah_oleh', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('order_status_histories');
    }

    public function down(): void
    {
        $this->forge->dropTable('order_status_histories', true);
    }
}
