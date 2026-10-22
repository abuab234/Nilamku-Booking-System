# 🚀 Panduan Deployment CI4 ke Shared Hosting (public_html / htdocs)

> **Konteks**: Layanan hosting gratis (seperti InfinityFree, Freehosting.com, atau shared hosting berbayar seperti Niagahoster, Rumahweb) biasanya hanya menyediakan satu root folder publik bernama `public_html` atau `htdocs`. Secara default, struktur CI4 mengharuskan folder `public/` yang terpisah dari `app/`, `system/`, dll. Panduan ini menjelaskan cara menyiasatinya tanpa perlu akses terminal server.

---

## 📁 Struktur Asli CI4 vs Struktur Hosting

```
# Struktur asli CI4 (development)          # Struktur target (shared hosting)
Nilamku_BookingSystem/                      public_html/
├── app/                                    ├── app/              ← pindahkan ke sini
├── public/                                 ├── system/           ← pindahkan ke sini
│   ├── index.php    ← entry point          ├── vendor/           ← pindahkan ke sini
│   └── .htaccess                           ├── index.php         ← dari public/
├── system/                                 ├── .htaccess         ← dari public/
├── vendor/                                 └── (aset publik lainnya)
├── writable/
└── .env
```

---

## 🔧 Langkah-Langkah Deployment

### Langkah 1: Edit `public/index.php`

Sebelum upload, ubah path di `public/index.php` agar menunjuk ke lokasi baru:

```php
// Baris ~14-17, ubah dari:
$pathsConfig = FCPATH . '../app/Config/Paths.php';

// ... dan di bawahnya:
$systemDirectory = 'system';  // relatif, tidak perlu diubah jika ikut struktur di bawah
```

Atau lebih mudah, **edit dua baris ini di `index.php`**:

```php
// SEBELUM (bawaan CI4):
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
// path relatif ke app dan system otomatis terdeteksi

// Pastikan baris ini mengarah ke folder yang benar:
// Jika app/, system/, vendor/ semua ada di public_html/, tidak perlu edit apapun.
```

> **✅ Kabar Baik**: Jika kamu meletakkan **semua folder** (`app/`, `system/`, `vendor/`, `writable/`, `.env`) langsung di dalam `public_html/`, dan file `index.php` + `.htaccess` juga ada di `public_html/`, **tidak perlu edit `index.php` sama sekali**. CI4 akan bekerja langsung.

---

### Langkah 2: Buat `.htaccess` di Root `public_html/`

File `.htaccess` dari folder `public/` CI4 sudah siap pakai. Salin ke `public_html/`:

```apache
# File: public_html/.htaccess
# (isi asli dari CI4 public/.htaccess)

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Arahkan semua request ke index.php (kecuali file/folder yang ada)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>

<IfModule !mod_rewrite.c>
    # Fallback jika mod_rewrite tidak aktif
    ErrorDocument 404 /index.php
</IfModule>
```

---

### Langkah 3: Edit `app/Config/Paths.php` (jika diperlukan)

Jika hosting kamu **tidak bisa** meletakkan `app/` dan `system/` di level yang sama dengan `public_html/`, edit path di `app/Config/Paths.php`:

```php
public string $systemDirectory = __DIR__ . '/../../system';
public string $appDirectory    = __DIR__ . '/..';
public string $writableDirectory = __DIR__ . '/../../writable';
```

---

### Langkah 4: Lindungi folder sensitif

Pastikan folder `app/`, `system/`, `vendor/`, `writable/` **tidak bisa diakses publik** via browser. Tambahkan `.htaccess` di masing-masing folder:

```apache
# File: app/.htaccess  |  system/.htaccess  |  vendor/.htaccess  |  writable/.htaccess
Deny from all
```

---

### Langkah 5: Upload via FTP/File Manager

**Urutan upload yang direkomendasikan:**

| # | Yang di-upload | Ke folder di hosting |
|---|---|---|
| 1 | `app/` | `public_html/app/` |
| 2 | `system/` | `public_html/system/` |
| 3 | `vendor/` | `public_html/vendor/` |
| 4 | `writable/` | `public_html/writable/` |
| 5 | `public/index.php` | `public_html/index.php` |
| 6 | `public/.htaccess` | `public_html/.htaccess` |
| 7 | `.env` | `public_html/.env` |

> ⚠️ **Jangan upload** `public/` sebagai folder — ambil isinya saja dan letakkan langsung di `public_html/`.

---

### Langkah 6: Konfigurasi `.env` untuk production

Update `.env` yang diupload:

```ini
CI_ENVIRONMENT = production   # ← ubah dari development

app.baseURL = 'https://namadomain.com/'

database.default.hostname = localhost   # ← biasanya localhost di shared hosting
database.default.database = nama_database_hosting
database.default.username = user_db_hosting
database.default.password = password_db_hosting
database.default.DBDriver = MySQLi
database.default.port = 3306
```

---

## ✅ Checklist Sebelum Go-Live

- [ ] `CI_ENVIRONMENT` sudah diset ke `production`
- [ ] `app.baseURL` sudah diisi dengan URL domain yang benar
- [ ] Kredensial database sudah diupdate
- [ ] Folder `writable/` memiliki permission `755` atau `775`
- [ ] File `.env` tidak bisa diakses publik (cek via browser — harus 403/404)
- [ ] `app/`, `system/`, `vendor/` tidak bisa diakses publik

---

## 🔗 Struktur Akhir di `public_html/` yang Direkomendasikan

```
public_html/
├── .env               ← konfigurasi sensitif
├── .htaccess          ← dari public/.htaccess CI4
├── index.php          ← dari public/index.php CI4
├── favicon.ico
├── app/               ← folder aplikasi CI4
│   └── .htaccess      ← Deny from all
├── system/            ← core CI4
│   └── .htaccess      ← Deny from all
├── vendor/            ← dependencies Composer
│   └── .htaccess      ← Deny from all
└── writable/          ← cache, logs, sessions
    └── .htaccess      ← Deny from all
```
