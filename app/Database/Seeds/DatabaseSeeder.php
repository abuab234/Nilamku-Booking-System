<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder — Entry point utama untuk semua seeder.
 * Jalankan: php spark db:seed DatabaseSeeder
 *
 * Urutan seeding mengikuti dependency FK:
 * Users → Categories → Items
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('UserSeeder');
        $this->call('CategorySeeder');
        $this->call('ItemSeeder');
    }
}
