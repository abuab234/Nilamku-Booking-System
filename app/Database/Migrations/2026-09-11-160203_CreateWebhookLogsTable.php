<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhookLogsTable extends Migration
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
            // event_type: 'order.created', 'order.ready', 'order.cancelled', dll
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            // Payload JSON yang dikirim ke webhook endpoint
            'payload' => [
                'type' => 'TEXT',
            ],
            'status_pengiriman' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'success', 'failed'],
                'default'    => 'pending',
            ],
            'percobaan_ke' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'default'  => 1,
            ],
            // Waktu retry berikutnya (NULL = tidak perlu retry / sudah sukses)
            'next_retry_at' => [
                'type'    => 'DATETIME',
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
        $this->forge->addKey('status_pengiriman'); // index untuk cron job query retry
        $this->forge->addKey('next_retry_at');

        // CASCADE DELETE: log ikut terhapus jika order dihapus
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('webhook_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('webhook_logs', true);
    }
}
