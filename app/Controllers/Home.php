<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\SettingModel;

class Home extends BaseController
{
    /**
     * Halaman Utama: Pemesanan Pelanggan (Katalog, Keranjang, Checkout)
     * GET /
     */
    public function index(): string
    {
        $settingModel = new SettingModel();

        $data = [
            'title'        => 'Nilamku - Pesan Sekarang, Ambil Tanpa Antre',
            'store_status' => $settingModel->getStoreStatus(),
        ];

        return view('home/index', $data);
    }

    /**
     * Halaman Pelacakan Status Pesanan
     * GET /track/{id}
     */
    public function track($id = null)
    {
        $orderId = (int) $id;
        if (! $orderId) {
            return redirect()->to('/')->with('error', 'ID pesanan tidak valid.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getOrderDetail($orderId);

        // Jika request meminta JSON (untuk fitur polling realtime via AJAX/fetch)
        if ($this->request->isAJAX() || $this->request->getGet('json') === '1') {
            if (! $order) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => "Pesanan #ORD-{$orderId} tidak ditemukan.",
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $order,
            ]);
        }

        if (! $order) {
            return redirect()->to('/')->with('error', "Pesanan #ORD-{$orderId} tidak ditemukan.");
        }

        $data = [
            'title' => "Lacak Pesanan #ORD-{$orderId} - Nilamku",
            'order' => $order,
        ];

        return view('home/track', $data);
    }
}
