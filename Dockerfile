FROM php:8.3-fpm

# 1. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libonig-dev libxml2-dev libpq-dev libgmp-dev libxslt-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql pdo_pgsql \
        mbstring exif pcntl bcmath gd zip xsl gmp intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# COPIAR PROYECTO
COPY . .

# Permitir que composer se ejecute como root y sin limite de memoria
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# 2. Instalar dependencias PHP
# AQUI ESTABA EL ERROR: Faltaba agregar --ignore-platform-reqs al final
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# Permisos Laravel
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Xdebug (Desactivado para producción)
# RUN pecl install xdebug && docker-php-ext-enable xdebug

EXPOSE 8080

# 3. Comando de inicio
CMD php artisan optimize:clear && \
    php artisan package:discover --ansi && \
    php artisan serve --host=0.0.0.0 --port=8080