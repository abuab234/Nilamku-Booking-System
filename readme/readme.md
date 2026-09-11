# Platform Pemesanan & Penjadwalan (Order-Ahead / Booking System)

## 1. Ringkasan Project

Sistem pemesanan dan penjadwalan berbasis web yang memungkinkan pelanggan memesan menu/layanan terlebih dahulu (*order-ahead*) dan memilih slot waktu tertentu, sehingga bisnis dapat mengelola antrean dan kapasitas secara otomatis tanpa bentrok jadwal.

**Cocok untuk:** restoran/kafe (pre-order & pickup), bisnis ritel (reservasi produk), dan penyedia jasa (booking layanan seperti salon, klinik, bengkel, dll).

**Masalah yang diselesaikan:**
- Antrean fisik yang menumpuk saat jam sibuk.
- Bentroknya jadwal/slot antar pelanggan.
- Kurangnya transparansi status pesanan bagi pelanggan.
- Proses konfirmasi manual yang lambat (tidak ada notifikasi otomatis).

---

## 2. Aktor / Pengguna Sistem

| Aktor | Deskripsi |
|---|---|
| **Pelanggan (Customer)** | Melihat katalog, memilih item, memilih slot waktu, melakukan pemesanan, memantau status pesanan. |
| **Admin/Staff (Merchant)** | Mengelola katalog (CRUD menu/layanan), mengatur slot waktu & kapasitas, mengubah status pesanan, melihat riwayat pesanan. |
| **Sistem (Automated)** | Mengirim notifikasi otomatis via webhook saat status pesanan berubah. |

---

## 3. Fitur Kunci

### 3.1 Katalog Menu/Layanan
- Daftar item (menu makanan atau layanan jasa) lengkap dengan nama, deskripsi, harga, foto, dan durasi (khusus layanan jasa).
- Filter kategori yang **responsif** (real-time, tanpa reload halaman) — misalnya kategori "Makanan Berat", "Minuman", "Potong Rambut", "Perawatan Wajah", dsb.
- Pencarian item berdasarkan nama (opsional, nice-to-have).
- Status ketersediaan item (tersedia / habis / nonaktif).

### 3.2 Pemilihan Jadwal / Slot Waktu
- Kalender atau daftar slot waktu yang tersedia (misalnya per 15/30/60 menit, tergantung konfigurasi bisnis).
- Sistem **mencegah overlapping**: satu slot memiliki kapasitas maksimum (jumlah pesanan/reservasi yang bisa diterima), dan slot otomatis tertutup jika kapasitas penuh.
- Validasi di sisi server (bukan hanya UI) agar tidak ada dua pesanan yang menempati slot yang sama melebihi kapasitas — perlu locking/transaction untuk mencegah race condition saat banyak pengguna memesan bersamaan.
- Admin dapat mengatur jam operasional, durasi slot, dan kapasitas per slot.

### 3.3 Manajemen Status Pesanan Bertingkat
Alur status pesanan mengikuti state machine berikut:

```
Pending → Diproses → Siap → Selesai
                   ↘
                    Dibatalkan (opsional, dari Pending/Diproses)
```

| Status | Arti |
|---|---|
| **Pending** | Pesanan baru masuk, menunggu konfirmasi/diterima oleh merchant. |
| **Diproses** | Pesanan sudah dikonfirmasi dan sedang disiapkan. |
| **Siap** | Pesanan selesai disiapkan, siap diambil/digunakan pelanggan. |
| **Selesai** | Pesanan sudah diambil/diselesaikan (transaksi tertutup). |
| **Dibatalkan** *(opsional)* | Pesanan dibatalkan oleh pelanggan atau merchant. |

- Setiap perubahan status harus tercatat (timestamp + siapa yang mengubah) untuk keperluan audit/riwayat.
- Perubahan status hanya bisa dilakukan searah (tidak bisa mundur), kecuali oleh admin dalam kondisi khusus.

### 3.4 Integrasi Notifikasi Otomatis (Webhook)
- Setiap kali status pesanan berubah, sistem memicu **webhook** ke layanan eksternal (contoh: email service, WhatsApp/Telegram Bot API, atau sistem pihak ketiga lain).
- Notifikasi minimal dikirim pada momen:
  - Pesanan berhasil dibuat (konfirmasi ke pelanggan).
  - Status berubah menjadi **Siap** (pemberitahuan untuk mengambil pesanan).
  - Pesanan **Dibatalkan** (jika ada).
- Payload webhook idealnya berisi: `order_id`, `status`, `customer_info`, `timestamp`, `items`.
- Perlu mekanisme retry sederhana jika pengiriman webhook gagal (misalnya 3x percobaan dengan backoff).

---

## 4. Model Data (Entitas Utama)

```
User (Customer/Admin)
 ├─ id, nama, email, no_telepon, role, password_hash

Category
 ├─ id, nama_kategori, urutan_tampil

Item (Menu/Layanan)
 ├─ id, category_id, nama, deskripsi, harga, durasi (khusus jasa), foto_url, status_tersedia

TimeSlot
 ├─ id, tanggal, jam_mulai, jam_selesai, kapasitas_maks, kapasitas_terpakai

Order (Pesanan)
 ├─ id, user_id, timeslot_id, status, total_harga, catatan, created_at, updated_at

OrderItem
 ├─ id, order_id, item_id, jumlah, subtotal

OrderStatusHistory
 ├─ id, order_id, status_lama, status_baru, diubah_oleh, timestamp

WebhookLog
 ├─ id, order_id, event_type, payload, status_pengiriman, percobaan_ke
```

---

## 5. Alur Pengguna (User Flow) — Pelanggan

1. Pelanggan membuka katalog → memfilter berdasarkan kategori.
2. Memilih satu atau lebih item → menambahkan ke keranjang.
3. Memilih slot waktu yang tersedia (sistem menampilkan hanya slot yang belum penuh).
4. Konfirmasi pesanan → sistem menyimpan pesanan dengan status **Pending**.
5. Webhook terpicu → pelanggan menerima notifikasi konfirmasi.
6. Pelanggan dapat memantau status pesanan secara real-time (Pending → Diproses → Siap → Selesai).

## 6. Alur Pengguna — Admin/Staff

1. Login ke dashboard admin.
2. Mengelola katalog (tambah/edit/hapus item & kategori).
3. Mengatur jadwal operasional & kapasitas slot waktu.
4. Melihat daftar pesanan masuk (terurut berdasarkan slot waktu/prioritas).
5. Mengubah status pesanan secara bertahap (Pending → Diproses → Siap → Selesai).
6. Setiap perubahan status otomatis memicu notifikasi ke pelanggan.

---

## 7. Tech Stack

| Layer | Pilihan |
|---|---|
| Backend Framework | **CodeIgniter 4** (PHP, MVC) |
| Frontend | View native CodeIgniter (PHP) + Bootstrap/Tailwind CSS, dipercantik dengan AJAX/Fetch API untuk interaksi filter kategori & update status tanpa reload |
| Database | MySQL/MariaDB (native support CodeIgniter, cocok untuk transaksi & locking slot) |
| ORM/Query | CodeIgniter **Query Builder** & **Model** bawaan |
| Realtime/Notifikasi | Webhook dikirim via **cURL** (library `CURLRequest` bawaan CodeIgniter) dari Controller/Service saat status berubah |
| Antrian/Retry Webhook | Tabel `webhook_log` sebagai queue sederhana + **CodeIgniter Task Scheduling** (Cron Job) untuk retry pengiriman yang gagal |
| Autentikasi | Session-based auth (native CodeIgniter Session Library) atau library Shield untuk autentikasi lebih lengkap |
| Validasi | CodeIgniter **Validation Library** bawaan |

### Catatan Implementasi Khusus CodeIgniter

- **Struktur folder** mengikuti konvensi CodeIgniter 4:
  ```
  app/
   ├─ Controllers/
   │   ├─ Customer/ (Katalog.php, Pesanan.php, Slot.php)
   │   └─ Admin/ (Katalog.php, Pesanan.php, Slot.php, Dashboard.php)
   ├─ Models/
   │   ├─ ItemModel.php
   │   ├─ CategoryModel.php
   │   ├─ TimeSlotModel.php
   │   ├─ OrderModel.php
   │   └─ OrderStatusHistoryModel.php
   ├─ Views/
   │   ├─ customer/
   │   └─ admin/
   ├─ Database/
   │   ├─ Migrations/
   │   └─ Seeds/
   └─ Libraries/ atau Services/
       └─ WebhookService.php
  ```
- **Pencegahan overlapping slot**: gunakan **Database Transaction** (`$db->transStart()` / `transComplete()`) dikombinasikan dengan `FOR UPDATE` row locking pada query pengecekan kapasitas slot sebelum insert pesanan baru, agar aman dari race condition saat banyak request bersamaan.
- **Migration & Seeder**: gunakan fitur *Migrations* CodeIgniter untuk membuat skema tabel di Bagian 4, dan *Seeder* untuk data dummy kategori/item saat development.
- **Webhook**: sebaiknya dibuat sebagai Service/Library terpisah (`WebhookService.php`) yang dipanggil dari Model/Controller setiap kali status pesanan berubah, agar logika pengiriman notifikasi terpusat dan mudah di-maintain.

---

## 8. Kebutuhan Non-Fungsional

- **Konsistensi data**: pencegahan overlapping slot harus dijamin di level database (transaction/locking), bukan hanya validasi frontend.
- **Skalabilitas**: mampu menangani lonjakan pesanan saat jam sibuk.
- **Auditability**: riwayat perubahan status pesanan tersimpan lengkap.
- **Responsif**: UI katalog & filter kategori harus cepat dan mobile-friendly.
- **Keandalan notifikasi**: webhook memiliki mekanisme retry & logging.

---

## 9. Potensi Pengembangan Lanjutan (Future Enhancements)

- Pembayaran online terintegrasi (payment gateway).
- Multi-cabang/multi-lokasi bisnis.
- Rating & review pasca-pesanan.
- Laporan analitik (jam sibuk, item terlaris, dsb).
- Push notification (selain webhook/email).

---

## 10. Catatan untuk AI Assistant (Antigravity AI)

Dokumen ini adalah spesifikasi awal untuk membangun **Platform Pemesanan & Penjadwalan**. Prioritas implementasi disarankan:

1. Struktur data & skema database (Bagian 4).
2. API untuk katalog + filter kategori.
3. Logika slot waktu & pencegahan overlapping (kritis — harus aman dari race condition).
4. State machine status pesanan (Bagian 3.3).
5. Integrasi webhook notifikasi (Bagian 3.4).
6. UI/UX pelanggan & dashboard admin.

Jika ada bagian yang ambigu, ikuti asumsi yang tertulis di dokumen ini sebagai default, kecuali pengguna memberikan instruksi lebih spesifik.