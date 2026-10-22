<?php

namespace App\Controllers\Api;

use App\Models\ItemModel;
use App\Models\CategoryModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * API Catalog Controller
 *
 * Menyediakan endpoint publik untuk katalog menu/layanan.
 * Mengikuti prinsip "Skinny Controller" — tidak ada Query Builder di sini,
 * semua logika query didelegasikan ke Model.
 *
 * Endpoint:
 *   GET /api/catalog              → Semua kategori + item aktif (grouped)
 *   GET /api/catalog/items        → Semua item aktif (flat list)
 *   GET /api/catalog/items/:id    → Ambil item berdasarkan category_id
 *   GET /api/catalog/search?q=... → Pencarian item berdasarkan nama
 */
class Catalog extends ResourceController
{
    protected $modelName = 'App\Models\ItemModel';
    protected $format    = 'json';

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/catalog
    // Mengembalikan JSON: array kategori, masing-masing berisi array items.
    // Digunakan sebagai data source utama halaman katalog pelanggan.
    // ──────────────────────────────────────────────────────────────────────
    public function index(): ResponseInterface
    {
        try {
            /** @var ItemModel $itemModel */
            $itemModel = new ItemModel();

            $catalog = $itemModel->getCatalogGroupedByCategory();

            return $this->respond([
                'status'  => 'success',
                'message' => 'Katalog berhasil dimuat.',
                'data'    => [
                    'total_kategori' => count($catalog),
                    'total_item'     => array_sum(array_map(fn($cat) => count($cat->items), $catalog)),
                    'katalog'        => $catalog,
                ],
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[API/Catalog::index] ' . $e->getMessage());

            return $this->respond([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat memuat katalog.',
                'error'   => (ENVIRONMENT === 'development') ? $e->getMessage() : null,
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/catalog/items
    // Flat list semua item aktif — alternatif ringan tanpa grouping.
    // ──────────────────────────────────────────────────────────────────────
    public function items(): ResponseInterface
    {
        try {
            /** @var ItemModel $itemModel */
            $itemModel = new ItemModel();

            $items = $itemModel->getAvailableItems();

            return $this->respond([
                'status'  => 'success',
                'message' => 'Daftar item berhasil dimuat.',
                'data'    => [
                    'total' => count($items),
                    'items' => $items,
                ],
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[API/Catalog::items] ' . $e->getMessage());

            return $this->failServerError('Terjadi kesalahan server.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/catalog/category/:id
    // Filter item berdasarkan category_id — dipanggil oleh AJAX filter.
    // ──────────────────────────────────────────────────────────────────────
    public function byCategory(int $categoryId): ResponseInterface
    {
        try {
            /** @var ItemModel $itemModel */
            $itemModel = new ItemModel();

            $items = $itemModel->getItemsByCategory($categoryId);

            /** @var CategoryModel $categoryModel */
            $categoryModel = new CategoryModel();
            $category      = $categoryModel->find($categoryId);

            if ($category === null) {
                return $this->failNotFound("Kategori dengan ID {$categoryId} tidak ditemukan.");
            }

            return $this->respond([
                'status'  => 'success',
                'message' => "Item untuk kategori '{$category->nama_kategori}' berhasil dimuat.",
                'data'    => [
                    'kategori' => $category,
                    'total'    => count($items),
                    'items'    => $items,
                ],
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[API/Catalog::byCategory] ' . $e->getMessage());

            return $this->failServerError('Terjadi kesalahan server.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/catalog/search?q=keyword
    // Pencarian item berdasarkan nama (nice-to-have per spec readme.md §3.1).
    // ──────────────────────────────────────────────────────────────────────
    public function search(): ResponseInterface
    {
        try {
            $keyword = trim((string) $this->request->getGet('q'));

            if (strlen($keyword) < 2) {
                return $this->fail('Parameter q minimal 2 karakter.', ResponseInterface::HTTP_BAD_REQUEST);
            }

            /** @var ItemModel $itemModel */
            $itemModel = new ItemModel();

            $items = $itemModel->searchByName($keyword);

            return $this->respond([
                'status'   => 'success',
                'message'  => "Hasil pencarian untuk '{$keyword}'.",
                'data'     => [
                    'keyword' => $keyword,
                    'total'   => count($items),
                    'items'   => $items,
                ],
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[API/Catalog::search] ' . $e->getMessage());

            return $this->failServerError('Terjadi kesalahan server.');
        }
    }
}
