<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTimeSlotsTable extends Migration
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'jam_mulai' => [
                'type' => 'TIME',
            ],
            'jam_selesai' => [
                'type' => 'TIME',
            ],
            'kapasitas_maks' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'default'  => 10,
            ],
            'kapasitas_terpakai' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'default'  => 0,
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
        // Index composite untuk query slot per tanggal (dipakai saat filter slot tersedia)
        $this->forge->addKey(['tanggal', 'jam_mulai']);

        $this->forge->createTable('time_slots');
    }

    public function down(): void
    {
        $this->forge->dropTable('time_slots', true);
    }
}
