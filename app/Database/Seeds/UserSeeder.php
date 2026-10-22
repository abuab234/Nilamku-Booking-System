<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Admin utama sistem
            [
                'nama'          => 'Administrator',
                'email'         => 'admin@nilamku.com',
                'no_telepon'    => '081234567890',
                'role'          => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            // Staff kasir
            [
                'nama'          => 'Staff Kasir',
                'email'         => 'kasir@nilamku.com',
                'no_telepon'    => '082345678901',
                'role'          => 'staff',
                'password_hash' => password_hash('staff123', PASSWORD_BCRYPT),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            // Customer dummy
            [
                'nama'          => 'Budi Santoso',
                'email'         => 'budi@example.com',
                'no_telepon'    => '089876543210',
                'role'          => 'customer',
                'password_hash' => password_hash('customer123', PASSWORD_BCRYPT),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
