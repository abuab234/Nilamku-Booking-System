<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nama_kategori',
        'urutan_tampil',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama_kategori' => 'required|min_length[2]|max_length[100]',
        'urutan_tampil' => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'nama_kategori' => [
            'required'   => 'Nama kategori wajib diisi.',
            'min_length' => 'Nama kategori minimal 2 karakter.',
            'max_length' => 'Nama kategori maksimal 100 karakter.',
        ],
    ];

    // ──────────────────────────────────────────────────────────
    // Custom Methods
    // ──────────────────────────────────────────────────────────

    /**
     * Ambil semua kategori yang relevan, diurutkan berdasarkan urutan_tampil.
     * Digunakan untuk filter kategori di halaman katalog (sidebar/tab).
     *
     * @return array<object>
     */
    public function getActiveCategories(): array
    {
        return $this->orderBy('urutan_tampil', 'ASC')
                    ->orderBy('nama_kategori', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil satu kategori beserta jumlah item aktif yang dimilikinya.
     * Berguna untuk menampilkan badge count di UI filter.
     *
     * @return array<object>
     */
    public function getCategoriesWithItemCount(): array
    {
        return $this->db->table('categories c')
                        ->select('c.id, c.nama_kategori, c.urutan_tampil, COUNT(i.id) AS jumlah_item')
                        ->join('items i', 'i.category_id = c.id AND i.status_tersedia = "tersedia"', 'left')
                        ->groupBy('c.id')
                        ->orderBy('c.urutan_tampil', 'ASC')
                        ->get()
                        ->getResult();
    }
}
