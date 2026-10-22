<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'category_id',
        'nama',
        'deskripsi',
        'harga',
        'durasi',
        'foto_url',
        'status_tersedia',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'category_id'     => 'required|integer|greater_than[0]',
        'nama'            => 'required|min_length[2]|max_length[150]',
        'harga'           => 'required|decimal|greater_than_equal_to[0]',
        'durasi'          => 'permit_empty|integer|greater_than[0]',
        'status_tersedia' => 'required|in_list[tersedia,habis,nonaktif]',
    ];

    protected $validationMessages = [
        'category_id' => ['required' => 'Kategori wajib dipilih.'],
        'nama'        => ['required' => 'Nama item wajib diisi.'],
        'harga'       => ['required' => 'Harga wajib diisi.', 'decimal' => 'Format harga tidak valid.'],
    ];

    // Callbacks
    protected $afterFind = ['formatImages'];

    /**
     * Otomatis memformat foto_url agar Google Drive links menjadi direct embed URL.
     */
    protected function formatImages(array $data)
    {
        if (! isset($data['data'])) {
            return $data;
        }

        if ($data['singleton']) {
            if (is_object($data['data']) && isset($data['data']->foto_url)) {
                $data['data']->foto_url = format_gdrive_image_url($data['data']->foto_url);
            } elseif (is_array($data['data']) && isset($data['data']['foto_url'])) {
                $data['data']['foto_url'] = format_gdrive_image_url($data['data']['foto_url']);
            }
        } else {
            foreach ($data['data'] as &$item) {
                if (is_object($item) && isset($item->foto_url)) {
                    $item->foto_url = format_gdrive_image_url($item->foto_url);
                } elseif (is_array($item) && isset($item['foto_url'])) {
                    $item['foto_url'] = format_gdrive_image_url($item['foto_url']);
                }
            }
        }

        return $data;
    }

    // ──────────────────────────────────────────────────────────
    // Custom Methods — Fat Model Pattern
    // ──────────────────────────────────────────────────────────

    /**
     * Ambil semua item aktif berdasarkan category_id.
     * Digunakan oleh filter kategori di katalog (AJAX request tanpa reload).
     *
     * @param int $categoryId
     * @return array<object>
     */
    public function getItemsByCategory(int $categoryId): array
    {
        return $this->where('category_id', $categoryId)
                    ->where('status_tersedia', 'tersedia')
                    ->orderBy('nama', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil semua item aktif (status: tersedia), dikelompokkan siap pakai di view.
     *
     * @return array<object>
     */
    public function getAvailableItems(): array
    {
        return $this->where('status_tersedia', 'tersedia')
                    ->orderBy('nama', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil katalog lengkap: semua kategori beserta item aktif di dalamnya.
     * Mengembalikan array kategori, masing-masing punya properti `items`.
     * Ini adalah query utama untuk endpoint GET /api/catalog.
     *
     * Strategi: 2 query terpisah (N+1 dihindari) — ambil semua kategori,
     * ambil semua item aktif, lalu grouping di PHP layer.
     *
     * @return array<object>
     */
    public function getCatalogGroupedByCategory(): array
    {
        // Query 1: semua kategori, diurutkan urutan_tampil
        $categories = $this->db->table('categories')
                               ->orderBy('urutan_tampil', 'ASC')
                               ->get()
                               ->getResult();

        if (empty($categories)) {
            return [];
        }

        // Query 2: semua item aktif dengan info kategori sekaligus (JOIN)
        $items = $this->db->table('items i')
                          ->select('i.*, c.nama_kategori')
                          ->join('categories c', 'c.id = i.category_id')
                          ->where('i.status_tersedia', 'tersedia')
                          ->orderBy('i.nama', 'ASC')
                          ->get()
                          ->getResult();

        // Grouping items ke dalam kategori masing-masing di PHP layer
        $itemsByCategory = [];
        foreach ($items as $item) {
            $item->foto_url = format_gdrive_image_url($item->foto_url);
            $itemsByCategory[$item->category_id][] = $item;
        }

        // Gabungkan: tambahkan properti `items` ke setiap kategori
        foreach ($categories as $category) {
            $category->items = $itemsByCategory[$category->id] ?? [];
        }

        return $categories;
    }

    /**
     * Cari item berdasarkan nama (pencarian realtime — nice-to-have per spec).
     *
     * @param string $keyword
     * @return array<object>
     */
    public function searchByName(string $keyword): array
    {
        return $this->like('nama', $keyword)
                    ->where('status_tersedia', 'tersedia')
                    ->orderBy('nama', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil semua item beserta info nama kategori untuk Dasbor Admin.
     *
     * @return array<object>
     */
    public function getAllItemsWithCategory(): array
    {
        return $this->select('items.*, c.nama_kategori')
                    ->join('categories c', 'c.id = items.category_id', 'left')
                    ->orderBy('items.category_id', 'ASC')
                    ->orderBy('items.id', 'ASC')
                    ->findAll();
    }

    /**
     * Cek apakah item aman untuk dihapus.
     * Item tidak boleh dihapus jika masih ada pesanan aktif (bukan selesai/dibatalkan)
     * yang mengandung item tersebut — untuk menjaga integritas data.
     *
     * @param int $itemId
     * @return array{safe: bool, active_count: int}
     */
    public function canBeDeleted(int $itemId): array
    {
        $activeCount = $this->db->table('order_items oi')
                               ->join('orders o', 'o.id = oi.order_id')
                               ->where('oi.item_id', $itemId)
                               ->whereNotIn('o.status', ['selesai', 'dibatalkan'])
                               ->countAllResults();

        return [
            'safe'         => $activeCount === 0,
            'active_count' => $activeCount,
        ];
    }
}

