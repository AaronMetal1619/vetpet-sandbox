FROM php:8.3-fpm

# Habilitar mod_rewrite y headers
RUN a2enmod rewrite headers

# Configurar DocumentRoot para Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Instalar dependencias de sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libonig-dev libxml2-dev libpq-dev libgmp-dev libxslt-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_mysql pdo_pgsql \
        mbstring exif pcntl bcmath gd zip xsl gmp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar composer.json y lock antes para cache
COPY composer.json composer.lock ./

# Instalar dependencias
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copiar proyecto completo
COPY . .

# Permisos
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Optimizar Laravel
RUN php artisan key:generate --force || true \
    && php artisan optimize || true

# --------------------------
# XDEBUG
# --------------------------
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo \"xdebug.start_with_request=yes\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo \"xdebug.client_host=host.docker.internal\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo \"xdebug.client_port=9003\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# --------------------------
# CORS para API (Apache level)
# --------------------------
RUN echo "<IfModule mod_headers.c>\n\
    Header always set Access-Control-Allow-Origin \"*\"\n\
    Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"\n\
    Header always set Access-Control-Allow-Headers \"Content-Type, Authorization, X-Requested-With\"\n\
</IfModule>" >> /etc/apache2/apache2.conf

EXPOSE 10000

CMD ["apache2-foreground"]
