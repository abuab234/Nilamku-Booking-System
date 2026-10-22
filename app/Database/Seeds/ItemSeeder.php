<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kategori dari DB agar tidak hardcode
        $makanan  = $this->db->table('categories')->where('nama_kategori', 'Makanan Berat')->get()->getRow();
        $minuman  = $this->db->table('categories')->where('nama_kategori', 'Minuman')->get()->getRow();
        $dessert  = $this->db->table('categories')->where('nama_kategori', 'Dessert & Snack')->get()->getRow();

        if (! $makanan || ! $minuman || ! $dessert) {
            throw new \RuntimeException('Categories not found. Jalankan CategorySeeder terlebih dahulu.');
        }

        $data = [
            // ── Makanan Berat ───────────────────────────────────────
            [
                'category_id'    => $makanan->id,
                'nama'           => 'Nasi Goreng Spesial',
                'deskripsi'      => 'Nasi goreng dengan telur mata sapi, ayam suwir, dan kerupuk. Porsi jumbo.',
                'harga'          => '25000.00',
                'durasi'         => null,
                'foto_url'       => null,
                'status_tersedia'=> 'tersedia',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'category_id'    => $makanan->id,
                'nama'           => 'Mie Ayam Bakso',
                'deskripsi'      => 'Mie ayam dengan topping bakso sapi, pangsit goreng, dan kuah kaldu.',
                'harga'          => '22000.00',
                'durasi'         => null,
                'foto_url'       => null,
                'status_tersedia'=> 'tersedia',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // ── Minuman ─────────────────────────────────────────────
            [
                'category_id'    => $minuman->id,
                'nama'           => 'Es Kieta Signature',
                'deskripsi'      => 'Es campur khas Nilamku: cincau hitam, kolang-kaling, sirup rose, dan susu.',
                'harga'          => '15000.00',
                'durasi'         => null,
                'foto_url'       => null,
                'status_tersedia'=> 'tersedia',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'category_id'    => $minuman->id,
                'nama'           => 'Jus Alpukat Coklat',
                'deskripsi'      => 'Jus alpukat segar dengan topping meses coklat dan susu kental manis.',
                'harga'          => '18000.00',
                'durasi'         => null,
                'foto_url'       => null,
                'status_tersedia'=> 'tersedia',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // ── Dessert & Snack ─────────────────────────────────────
            [
                'category_id'    => $dessert->id,
                'nama'           => 'Pisang Goreng Keju',
                'deskripsi'      => 'Pisang raja goreng crispy dengan topping keju parut dan susu coklat.',
                'harga'          => '12000.00',
                'durasi'         => null,
                'foto_url'       => null,
                'status_tersedia'=> 'tersedia',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('items')->insertBatch($data);
    }
}
