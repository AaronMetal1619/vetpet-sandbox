FROM php:8.2-apache

# 1. Habilitar módulos de Apache
RUN a2enmod rewrite headers proxy_http

# 2. Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    wget git unzip libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libgmp-dev libxslt-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd bcmath gmp zip xsl mbstring exif pdo_pgsql

# 3. Instalar Composer (optimizado)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 4. Copiar solo los archivos necesarios para composer primero
COPY composer.json composer.lock artisan /var/www/html/

# 5. Instalar dependencias (capa separada para cache)
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 6. Copiar el resto del código
COPY . /var/www/html/

# 7. Configurar permisos (optimizado)
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage bootstrap/cache

# 8. Configurar Apache para Laravel
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# 9. Instalar y configurar Xdebug (como lo tenías)
RUN pecl install xdebug-3.3.2 && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80
CMD ["apache2-foreground"]
