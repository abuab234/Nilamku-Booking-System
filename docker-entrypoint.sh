#!/bin/bash
# ============================================================
# docker-entrypoint.sh
# Dipanggil setiap kali container nilamku_app start.
# Memastikan folder writable/ selalu punya permission yang benar.
# ============================================================

# Fix permissions pada folder writable (diperlukan karena bind mount dari Windows)
chown -R www-data:www-data /var/www/html/writable 2>/dev/null || true
chmod -R 775 /var/www/html/writable 2>/dev/null || true

# Jalankan perintah default Apache (diteruskan dari CMD image parent)
exec apache2-foreground "$@"
