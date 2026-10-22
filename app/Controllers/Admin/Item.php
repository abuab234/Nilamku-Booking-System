<?php

namespace App\Controllers\Admin;

use App\Models\ItemModel;
use CodeIgniter\Controller;

class Item extends Controller
{
    protected ItemModel $itemModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
    }

    /**
     * Tambah item menu baru.
     * POST /admin/items/create
     */
    public function create()
    {
        $rules = [
            'nama'            => 'required|min_length[2]|max_length[150]',
            'category_id'     => 'required|integer|greater_than[0]',
            'harga'           => 'required|numeric|greater_than_equal_to[0]',
            'deskripsi'       => 'permit_empty',
            'durasi'          => 'permit_empty|integer|greater_than[0]',
            'foto_url'        => 'permit_empty|valid_url_strict',
            'status_tersedia' => 'permit_empty|in_list[tersedia,habis,nonaktif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', implode(' ', $this->validator->getErrors()))
                             ->withInput();
        }

        $data = [
            'nama'            => trim($this->request->getPost('nama')),
            'category_id'     => (int) $this->request->getPost('category_id'),
            'harga'           => (float) $this->request->getPost('harga'),
            'deskripsi'       => trim($this->request->getPost('deskripsi') ?? ''),
            'durasi'          => $this->request->getPost('durasi') ? (int) $this->request->getPost('durasi') : null,
            'foto_url'        => format_gdrive_image_url($this->request->getPost('foto_url')),
            'status_tersedia' => $this->request->getPost('status_tersedia') ?: 'tersedia',
        ];

        $inserted = $this->itemModel->insert($data);

        if (! $inserted) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', 'Gagal menambahkan menu baru ke database.');
        }

        return redirect()->to('/admin/dashboard#menu')
                         ->with('success', 'Menu "' . esc($data['nama']) . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui data item menu.
     * POST /admin/items/update/{id}
     */
    public function update($id = null)
    {
        $item = $this->itemModel->find((int) $id);
        if (! $item) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', 'Item menu tidak ditemukan.');
        }

        $rules = [
            'nama'            => 'required|min_length[2]|max_length[150]',
            'category_id'     => 'required|integer|greater_than[0]',
            'harga'           => 'required|numeric|greater_than_equal_to[0]',
            'deskripsi'       => 'permit_empty',
            'durasi'          => 'permit_empty|integer|greater_than[0]',
            'foto_url'        => 'permit_empty|valid_url_strict',
            'status_tersedia' => 'permit_empty|in_list[tersedia,habis,nonaktif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', implode(' ', $this->validator->getErrors()))
                             ->withInput();
        }

        $data = [
            'nama'            => trim($this->request->getPost('nama')),
            'category_id'     => (int) $this->request->getPost('category_id'),
            'harga'           => (float) $this->request->getPost('harga'),
            'deskripsi'       => trim($this->request->getPost('deskripsi') ?? ''),
            'durasi'          => $this->request->getPost('durasi') ? (int) $this->request->getPost('durasi') : null,
            'foto_url'        => format_gdrive_image_url($this->request->getPost('foto_url')),
            'status_tersedia' => $this->request->getPost('status_tersedia') ?: $item->status_tersedia,
        ];

        $this->itemModel->update((int) $id, $data);

        return redirect()->to('/admin/dashboard#menu')
                         ->with('success', 'Menu "' . esc($data['nama']) . '" berhasil diperbarui.');
    }

    /**
     * Toggle atau ubah status ketersediaan item menu.
     * POST /admin/items/toggle-status/{id}
     */
    public function toggleStatus($id = null)
    {
        $item = $this->itemModel->find((int) $id);
        if (! $item) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', 'Item menu tidak ditemukan.');
        }

        $targetStatus = $this->request->getPost('status_tersedia');

        if ($targetStatus && in_array($targetStatus, ['tersedia', 'habis', 'nonaktif'], true)) {
            $newStatus = $targetStatus;
        } else {
            $newStatus = ($item->status_tersedia === 'tersedia') ? 'habis' : 'tersedia';
        }

        $this->itemModel->update((int) $id, ['status_tersedia' => $newStatus]);

        return redirect()->to('/admin/dashboard#menu')
                         ->with('success', 'Status menu "' . esc($item->nama) . '" diubah menjadi ' . $newStatus . '.');
    }

    /**
     * Hapus item menu dari katalog.
     * POST /admin/items/delete/{id}
     *
     * Safeguard: item tidak dapat dihapus jika masih ada pesanan aktif
     * (pending/diproses/siap) yang mengandung item tersebut.
     */
    public function delete($id = null)
    {
        $item = $this->itemModel->find((int) $id);
        if (! $item) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', 'Item menu tidak ditemukan atau sudah dihapus sebelumnya.');
        }

        // Cek apakah item aman untuk dihapus (tidak ada pesanan aktif yang mengandungnya)
        $check = $this->itemModel->canBeDeleted((int) $id);
        if (! $check['safe']) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', 'Tidak dapat menghapus menu "' . esc($item->nama) . '" karena masih ada ' . $check['active_count'] . ' pesanan aktif yang mengandung menu ini. Selesaikan atau batalkan pesanan tersebut terlebih dahulu.');
        }

        $deleted = $this->itemModel->delete((int) $id);

        if (! $deleted) {
            return redirect()->to('/admin/dashboard#menu')
                             ->with('error', 'Gagal menghapus menu "' . esc($item->nama) . '" dari database.');
        }

        return redirect()->to('/admin/dashboard#menu')
                         ->with('success', 'Menu "' . esc($item->nama) . '" berhasil dihapus dari katalog.');
    }
}
