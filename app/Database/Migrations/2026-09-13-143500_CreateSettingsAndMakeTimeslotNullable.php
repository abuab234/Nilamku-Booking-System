<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsAndMakeTimeslotNullable extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel settings jika belum ada
        if (! $this->db->tableExists('settings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'setting_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'unique'     => true,
                ],
                'setting_value' => [
                    'type' => 'TEXT',
                    'null' => true,
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
            $this->forge->createTable('settings');

            // Nilai awal: Toko buka
            $this->db->table('settings')->insert([
                'setting_key'   => 'store_status',
                'setting_value' => 'buka',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        // 2. Ubah kolom timeslot_id pada tabel orders menjadi nullable
        $this->forge->modifyColumn('orders', [
            'timeslot_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('settings', true);

        $this->forge->modifyColumn('orders', [
            'timeslot_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
