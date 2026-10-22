#!/bin/bash
# ============================================================
# setup.sh — Dijalankan di dalam container app setelah build
# Menginstall CI4, mengkonfigurasi .env dan App.php
# ============================================================

set -e  # Stop on error

echo "=========================================="
echo " Nilamku Booking System — CI4 Setup"
echo "=========================================="

# ── 1. Install CodeIgniter 4 via Composer ────────────────────
echo ""
echo "[1/4] Installing CodeIgniter 4 via Composer..."
cd /var/www/html

# Install CI4 di direktori saat ini (sudah ada composer.json dari appstarter)
composer create-project codeigniter4/appstarter /tmp/ci4_install --no-interaction --no-progress
# Salin semua file (kecuali .git) ke working directory
cp -rn /tmp/ci4_install/. /var/www/html/
echo "      ✓ CodeIgniter 4 installed"

# ── 2. Setup .env dari env ────────────────────────────────────
echo ""
echo "[2/4] Configuring .env file..."
if [ -f /var/www/html/env ]; then
    cp /var/www/html/env /var/www/html/.env

    # Aktifkan CI_ENVIRONMENT = development
    sed -i 's/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = development/' /var/www/html/.env

    # Aktifkan dan isi konfigurasi database (host = nama service Docker 'db')
    sed -i 's/# database.default.hostname = localhost/database.default.hostname = db/' /var/www/html/.env
    sed -i 's/# database.default.database = ci4/database.default.database = /' /var/www/html/.env
    sed -i 's/# database.default.username = root/database.default.username = nilamku_user/' /var/www/html/.env
    sed -i 's/# database.default.password = root/database.default.password = nilamku_pass/' /var/www/html/.env
    sed -i 's/# database.default.DBDriver = MySQLi/database.default.DBDriver = MySQLi/' /var/www/html/.env
    sed -i 's/# database.default.DBPrefix =/database.default.DBPrefix =/' /var/www/html/.env
    sed -i 's/# database.default.port = 3306/database.default.port = 3306/' /var/www/html/.env

    echo "      ✓ .env configured"
else
    echo "      ✗ ERROR: File 'env' tidak ditemukan!"
    exit 1
fi

# ── 3. Set timezone ke Asia/Jakarta di App.php ───────────────
echo ""
echo "[3/4] Setting timezone to Asia/Jakarta..."
sed -i "s/public string \$appTimezone = 'America\/Chicago'/public string \$appTimezone = 'Asia\/Jakarta'/" \
    /var/www/html/app/Config/App.php
echo "      ✓ Timezone set to Asia/Jakarta"

# ── 4. Set permissions ────────────────────────────────────────
echo ""
echo "[4/4] Setting file permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable
echo "      ✓ Permissions set"

echo ""
echo "=========================================="
echo " ✅ Setup selesai! "
echo " Akses aplikasi di: http://localhost:8080"
echo "=========================================="
