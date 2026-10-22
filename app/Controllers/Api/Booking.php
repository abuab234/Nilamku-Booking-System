<?php

namespace App\Controllers\Api;

use App\Models\OrderModel;
use App\Models\ItemModel;
use App\Models\TimeSlotModel;
use App\Models\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * API Booking Controller — Skinny Controller
 *
 * Menerima request pemesanan, melakukan validasi input,
 * lalu mendelegasikan seluruh logika bisnis ke OrderModel::createBooking().
 *
 * Endpoint:
 *   POST /api/booking/create
 *
 * Expected JSON Body:
 * {
 *   "user_id"     : 1,
 *   "slot_id"     : 1,
 *   "cart_items"  : [
 *     { "item_id": 1, "jumlah": 2, "subtotal": 50000.00 },
 *     { "item_id": 3, "jumlah": 1, "subtotal": 15000.00 }
 *   ],
 *   "total_harga" : 65000.00,
 *   "catatan"     : "Tanpa MSG, less sugar" (opsional)
 * }
 */
class Booking extends ResourceController
{
    protected $format = 'json';

    // ──────────────────────────────────────────────────────────────────────
    // POST /api/booking/create
    // ──────────────────────────────────────────────────────────────────────
    public function create(): ResponseInterface
    {
        // ── 1. Parse Input: dukung JSON maupun multipart/form-data ──
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            try {
                $body = $this->request->getJSON(true) ?? [];
            } catch (\Throwable $e) {
                $body = [];
            }
        } else {
            $body = $this->request->getPost() ?? [];
        }

        // ── 0. Cek Status Operasional Toko ─────────────────────
        $settingModel = new SettingModel();
        if ($settingModel->getStoreStatus() === 'tutup') {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Mohon maaf, Nilamku saat ini sedang TUTUP dan tidak beroperasi. Pesanan baru tidak dapat diproses saat ini.',
            ], ResponseInterface::HTTP_FORBIDDEN);
        }

        if (empty($body)) {
            return $this->fail('Data pemesanan tidak boleh kosong. Pastikan form data atau JSON terisi.', 400);
        }

        // Parse cart_items jika dikirim sebagai JSON string (misal dari FormData browser)
        if (isset($body['cart_items']) && is_string($body['cart_items'])) {
            $decoded = json_decode($body['cart_items'], true);
            if (is_array($decoded)) {
                $body['cart_items'] = $decoded;
            }
        }

        // Normalisasi angka desimal dengan format koma (e.g. "2,0" -> 2.0)
        if (isset($body['jarak_km'])) {
            $body['jarak_km'] = (float) str_replace(',', '.', (string) $body['jarak_km']);
        }
        if (isset($body['total_harga'])) {
            $body['total_harga'] = (float) str_replace(',', '.', (string) $body['total_harga']);
        }

        // Resolusi user_id jika pemesanan berasal dari antarmuka pelanggan publik
        if (empty($body['user_id'])) {
            $nama  = trim((string) ($body['nama'] ?? $body['name'] ?? 'Pelanggan'));
            $phone = preg_replace('/[^0-9]/', '', (string) ($body['no_wa'] ?? $body['phone'] ?? $body['no_telepon'] ?? ''));

            if (! empty($phone)) {
                $userModel = new \App\Models\UserModel();
                $cleanPhone = $phone ?: (string) time();
                $email = 'cust_' . $cleanPhone . '@nilamku.local';

                $existingUser = $userModel->groupStart()
                                          ->where('no_telepon', $phone)
                                          ->orWhere('email', $email)
                                          ->groupEnd()
                                          ->first();

                if ($existingUser) {
                    $body['user_id'] = (int) $existingUser->id;
                    $updateData = [];
                    if (empty($existingUser->no_telepon)) {
                        $updateData['no_telepon'] = $phone;
                    }
                    if ($nama && ($existingUser->nama === 'Pelanggan' || str_starts_with($existingUser->nama, 'Pelanggan '))) {
                        $updateData['nama'] = $nama;
                    }
                    if (! empty($updateData)) {
                        $userModel->update($existingUser->id, $updateData);
                    }
                } else {
                    $newUserId = $userModel->insert([
                        'nama'          => $nama ?: 'Pelanggan ' . substr($phone, -4),
                        'email'         => $email,
                        'no_telepon'    => $phone,
                        'role'          => 'customer',
                        'password_hash' => password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT),
                    ]);
                    $body['user_id'] = (int) $newUserId;
                }
            } elseif (session()->get('logged_in')) {
                $body['user_id'] = (int) session()->get('id');
            } else {
                $body['user_id'] = 3; // Fallback customer seeder
            }
        }

        // ── 2. Handle File Upload: Bukti Transfer ──────────────
        $buktiTransferPath = null;
        $fileBukti = $this->request->getFile('bukti_transfer');

        if ($fileBukti && $fileBukti->isValid() && ! $fileBukti->hasMoved()) {
            $fileValidation = \Config\Services::validation();
            $fileValidation->setRules([
                'bukti_transfer' => [
                    'label' => 'Bukti Transfer',
                    'rules' => 'uploaded[bukti_transfer]|is_image[bukti_transfer]|max_size[bukti_transfer,2048]|ext_in[bukti_transfer,png,jpg,jpeg]|mime_in[bukti_transfer,image/png,image/jpeg,image/jpg]',
                    'errors' => [
                        'max_size' => 'Ukuran berkas bukti transfer maksimal 2MB.',
                        'mime_in'  => 'Format gambar bukti transfer harus PNG, JPG, atau JPEG.',
                        'ext_in'   => 'Ekstensi berkas bukti transfer harus .png, .jpg, atau .jpeg.',
                        'is_image' => 'Berkas yang diunggah harus berupa gambar yang valid.',
                    ],
                ],
            ]);

            if (! $fileValidation->withRequest($this->request)->run()) {
                return $this->fail($fileValidation->getErrors(), ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
            }

            $uploadDir = FCPATH . 'uploads/bukti/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newName = $fileBukti->getRandomName();
            $fileBukti->move($uploadDir, $newName);
            $buktiTransferPath = 'uploads/bukti/' . $newName;
        }

        // ── 3. Validasi Keranjang (Cart Items) ───────────────────
        if (! isset($body['cart_items']) || ! is_array($body['cart_items']) || empty($body['cart_items'])) {
            return $this->fail('Keranjang belanja tidak boleh kosong.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        foreach ($body['cart_items'] as $idx => $cItem) {
            if (! is_array($cItem) || empty($cItem['item_id']) || empty($cItem['jumlah'])) {
                return $this->fail("Item ke-" . ($idx + 1) . " di keranjang tidak valid.", ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ((int)$cItem['item_id'] <= 0 || (int)$cItem['jumlah'] <= 0) {
                return $this->fail("Jumlah item ke-" . ($idx + 1) . " harus lebih dari 0.", ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // ── 4. Validasi Field Wajib ─────────────────────────────
        $validationRules = [
            'user_id'              => 'required|integer|greater_than[0]',
            'slot_id'              => 'permit_empty|integer',
            'metode_layanan'       => 'permit_empty|in_list[dine_in,delivery]',
            'metode_pembayaran'    => 'permit_empty|in_list[transfer]',
        ];

        $metodeLayanan    = (string) ($body['metode_layanan'] ?? 'dine_in');
        $metodePembayaran = 'transfer'; // Hanya mendukung metode transfer DANA

        if ($metodeLayanan === 'delivery') {
            $validationRules['alamat_pengiriman'] = 'required|min_length[5]';
            $validationRules['jarak_km']          = 'required|numeric|greater_than_equal_to[0]';
        }

        if ($buktiTransferPath === null) {
            return $this->fail([
                'bukti_transfer' => 'Berkas bukti transfer DANA wajib diunggah sebelum pesanan dapat diproses.'
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validation = \Config\Services::validation();
        $validation->setRules($validationRules);

        if (! $validation->run($body)) {
            return $this->fail($validation->getErrors(), ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userId           = (int) $body['user_id'];
        $slotId           = !empty($body['slot_id']) ? (int) $body['slot_id'] : null;
        $cartItems        = (array) $body['cart_items'];
        $totalHarga       = (float) ($body['total_harga'] ?? 0);
        $catatan          = trim((string) ($body['catatan'] ?? ''));
        $alamatPengiriman = ($metodeLayanan === 'delivery') ? trim((string) ($body['alamat_pengiriman'] ?? '')) : null;
        $jarakKm          = ($metodeLayanan === 'delivery') ? (float) ($body['jarak_km'] ?? 0) : 0.00;

        // ── 5. Validasi item_id yang dikirim ada di database ───
        $itemModel  = new ItemModel();
        $itemIds    = array_column($cartItems, 'item_id');
        $existItems = $itemModel->select('id')->whereIn('id', $itemIds)
                                ->where('status_tersedia', 'tersedia')
                                ->findAll();

        if (count($existItems) !== count(array_unique($itemIds))) {
            return $this->fail(
                'Satu atau lebih item tidak tersedia atau tidak ditemukan. Refresh katalog dan coba lagi.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // ── 6. Delegasikan ke OrderModel::createBooking() ─────
        $orderModel = new OrderModel();
        $orderId    = $orderModel->createBooking(
            userId:           $userId,
            slotId:           $slotId,
            cartItems:        $cartItems,
            totalHarga:       $totalHarga,
            catatan:          $catatan,
            metodeLayanan:    $metodeLayanan,
            alamatPengiriman: $alamatPengiriman,
            jarakKm:          $jarakKm,
            metodePembayaran: $metodePembayaran,
            buktiTransfer:    $buktiTransferPath
        );

        // ── 7. Respons Berdasarkan Hasil Transaksi ─────────────
        if ($orderId === false) {
            return $this->respond([
                'status'  => 'error',
                'message' => $orderModel->bookingError ?: 'Pemesanan gagal. Silakan coba lagi.',
                'data'    => null,
            ], ResponseInterface::HTTP_CONFLICT);
        }

        // Ambil detail order yang baru dibuat
        $orderDetail = $orderModel->getOrderDetail($orderId);

        return $this->respond([
            'status'        => 'success',
            'message'       => "Pesanan #ORD-{$orderId} berhasil dibuat! Nomor Antrean: {$orderDetail->nomor_antrian}.",
            'order_id'      => $orderId,
            'nomor_antrian' => $orderDetail->nomor_antrian,
            'data'          => [
                'order_id'      => $orderId,
                'nomor_antrian' => $orderDetail->nomor_antrian,
                'order'         => $orderDetail,
            ],
        ], ResponseInterface::HTTP_CREATED);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/booking/slots?date=YYYY-MM-DD
    // Endpoint publik untuk melihat slot yang tersedia pada tanggal tertentu.
    // ──────────────────────────────────────────────────────────────────────
    public function slots(): ResponseInterface
    {
        $date = $this->request->getGet('date');

        if (empty($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->fail('Parameter date wajib diisi dengan format YYYY-MM-DD.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $timeSlotModel = new TimeSlotModel();
        $slots         = $timeSlotModel->getSlotsForDate($date);

        return $this->respond([
            'status'  => 'success',
            'message' => "Slot untuk tanggal {$date}.",
            'data'    => [
                'date'  => $date,
                'total' => count($slots),
                'slots' => $slots,
            ],
        ], ResponseInterface::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/store-status
    // Endpoint publik untuk mengecek status operasional toko ('buka' / 'tutup')
    // ──────────────────────────────────────────────────────────────────────
    public function storeStatus(): ResponseInterface
    {
        $settingModel = new SettingModel();
        $status = $settingModel->getStoreStatus();

        return $this->respond([
            'status'       => 'success',
            'store_status' => $status,
            'is_open'      => ($status === 'buka'),
            'message'      => ($status === 'buka') ? 'Toko buka dan siap melayani.' : 'Toko sedang tutup dan tidak beroperasi.',
        ], ResponseInterface::HTTP_OK);
    }
}
