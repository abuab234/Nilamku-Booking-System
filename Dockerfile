# ============================================================
# Dockerfile — Nilamku Booking System (CodeIgniter 4)
# Base image: PHP 8.2 + Apache (Debian)
# ============================================================

FROM php:8.2-apache

# ── System dependencies ──────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# ── PHP Extensions wajib CodeIgniter 4 ──────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        xml \
        curl \
        gd \
        zip \
        opcache \
        bcmath

# ── Composer ─────────────────────────────────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ── Apache: aktifkan mod_rewrite ─────────────────────────────
RUN a2enmod rewrite

# ── Apache: arahkan DocumentRoot ke public/ CI4 ──────────────
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/>|<Directory /var/www/html/>|g' \
        /etc/apache2/apache2.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' \
        /etc/apache2/apache2.conf

# ── PHP config ────────────────────────────────────────────────
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

# ── Working directory ─────────────────────────────────────────
WORKDIR /var/www/html

# ── Expose port 80 ───────────────────────────────────────────
EXPOSE 80

# ── Entrypoint: fix writable/ permissions on every start ─────
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

