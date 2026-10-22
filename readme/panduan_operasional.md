# Panduan Operasional Nilamku Booking System

Dokumen ini berisi panduan praktis untuk mematikan (*shutdown*) dan menyalakan kembali (*startup*) seluruh layanan sistem Nilamku Booking System pada Docker di komputer Windows.

---

## 1. Cara Menutup Project Hari Ini (Graceful Shutdown)

Sebelum menutup terminal, Docker Desktop, atau laptop/PC, jalankan perintah berikut di PowerShell atau Command Prompt pada direktori project (`c:\Users\Windows\Nilamku_BookingSystem`):

```powershell
docker compose stop
```

> **Catatan:**
> Perintah `docker compose stop` hanya menghentikan kontainer yang sedang berjalan tanpa menghapus data atau kontainernya. Seluruh data pesanan, nomor antrean, bukti transfer, dan konfigurasi database tetap aman 100% di dalam Docker Volume.

Jika Anda ingin mematikan dan melepas jaringan kontainer secara penuh, gunakan:
```powershell
docker compose down
```
*(Catatan: Jangan gunakan flag `-v` agar data database MySQL Anda tidak terhapus).*

Setelah itu, Anda dapat menutup aplikasi Docker Desktop dan Antigravity IDE dengan tenang.

---

## 2. Cara Memulai Kembali Project Besok (Startup Flow)

Saat ingin melanjutkan pekerjaan atau menjalankan aplikasi besok:

### Langkah 1: Buka Docker Desktop
Pastikan aplikasi **Docker Desktop** di Windows sudah terbuka dan engine Docker berstatus *Engine running* (ikon paus hijau di taskbar).

### Langkah 2: Nyalakan Container Project
Buka PowerShell di direktori `c:\Users\Windows\Nilamku_BookingSystem`, lalu jalankan:

```powershell
docker compose up -d
```

Output yang diharapkan:
```text
[+] Running 3/3
 ✔ Container nilamku_db         Healthy
 ✔ Container nilamku_app        Started
 ✔ Container nilamku_pma        Started
```

### Langkah 3: Verifikasi Status Container
Pastikan semua container aktif:
```powershell
docker compose ps
```

---

## 3. Daftar Akses URL & Kredensial Akun

Setelah container aktif, semua layanan langsung dapat dibuka melalui browser:

| Layanan | URL Akses | Keterangan & Akun |
|---|---|---|
| **Aplikasi Pelanggan** | [http://localhost:8000/](http://localhost:8000/) | Katalog menu, keranjang belanja, checkout DANA, FAQ & CS |
| **Lacak Pesanan** | [http://localhost:8000/track/{id}](http://localhost:8000/track/17) | Halaman antrean, status pesanan realtime & bukti transfer |
| **Login Dasbor Admin** | [http://localhost:8000/login](http://localhost:8000/login) | **Email:** `admin@nilamku.com`<br>**Password:** `admin123` |
| **Login Kasir / Staff** | [http://localhost:8000/login](http://localhost:8000/login) | **Email:** `kasir@nilamku.com`<br>**Password:** `kasir123` |
| **phpMyAdmin** | [http://localhost:8001/](http://localhost:8001/) | **Server:** `db`<br>**User:** `nilamku_user`<br>**Password:** `nilamku_pass` |

---

## 4. Troubleshooting Cepat (Jika Ada Kendala)

- **Folder `writable/` tidak bisa menulis:**
  Jalankan perintah ini di PowerShell:
  ```powershell
  docker exec nilamku_app chmod -R 777 writable public/uploads
  ```
- **Melihat Log Error Backend:**
  ```powershell
  docker logs --tail 50 -f nilamku_app
  ```
