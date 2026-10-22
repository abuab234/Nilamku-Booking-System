<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinancialRecordsTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('financial_records', true);

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
                'null'       => true,
            ],
            'nomor_antrian' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'nama_pelanggan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'no_wa' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'waktu' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'metode_layanan' => [
                'type'       => 'ENUM',
                'constraint' => ['dine_in', 'delivery'],
                'default'    => 'dine_in',
            ],
            'metode_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'transfer',
            ],
            'subtotal_menu' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'biaya_ongkir' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'total_pendapatan' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'rincian_item' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('tanggal');
        $this->forge->createTable('financial_records', true);

        // ── Backfill data pesanan yang sudah ada ke financial_records ────────
        $db = \Config\Database::connect();
        $orders = $db->table('orders')
                     ->select('orders.*, users.nama as user_nama, users.no_telepon as user_phone')
                     ->join('users', 'users.id = orders.user_id', 'left')
                     ->where('orders.status !=', 'dibatalkan')
                     ->get()
                     ->getResult();

        foreach ($orders as $order) {
            // Ambil item pesanan
            $items = $db->table('order_items')
                        ->select('order_items.*, items.nama as nama_item, items.harga as harga_item')
                        ->join('items', 'items.id = order_items.item_id', 'left')
                        ->where('order_id', $order->id)
                        ->get()
                        ->getResult();

            $subtotalMenu = 0.00;
            $itemsSummary = [];
            foreach ($items as $it) {
                $subtotalMenu += (float) $it->subtotal;
                $hargaSatuan   = isset($it->harga_item) ? (float)$it->harga_item : ((int)$it->jumlah > 0 ? ((float)$it->subtotal / (int)$it->jumlah) : 0);
                $itemsSummary[] = [
                    'nama'     => $it->nama_item ?? "Item #{$it->item_id}",
                    'jumlah'   => (int) $it->jumlah,
                    'harga'    => $hargaSatuan,
                    'subtotal' => (float) $it->subtotal,
                ];
            }

            $ongkir = (float) ($order->biaya_ongkir ?? 0.00);
            $total  = (float) ($order->total_harga ?? ($subtotalMenu + $ongkir));
            $tgl    = date('Y-m-d', strtotime($order->created_at ?? 'now'));
            $wkt    = date('H:i:s', strtotime($order->created_at ?? 'now'));

            $nama = !empty($order->user_nama) ? $order->user_nama : 'Pelanggan';
            $wa   = !empty($order->user_phone) ? $order->user_phone : '-';

            $db->table('financial_records')->insert([
                'order_id'         => $order->id,
                'nomor_antrian'    => $order->nomor_antrian ?? 'ORD-' . $order->id,
                'nama_pelanggan'   => $nama,
                'no_wa'            => $wa,
                'tanggal'          => $tgl,
                'waktu'            => $wkt,
                'metode_layanan'   => $order->metode_layanan ?? 'dine_in',
                'metode_pembayaran'=> $order->metode_pembayaran ?? 'transfer',
                'subtotal_menu'    => $subtotalMenu,
                'biaya_ongkir'     => $ongkir,
                'total_pendapatan' => $total,
                'rincian_item'     => json_encode($itemsSummary, JSON_UNESCAPED_UNICODE),
                'catatan'          => $order->catatan,
                'created_at'       => $order->created_at ?? date('Y-m-d H:i:s'),
                'updated_at'       => $order->updated_at ?? date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('financial_records', true);
    }
}
