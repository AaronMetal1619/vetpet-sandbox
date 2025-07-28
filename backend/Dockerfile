FROM php:8.2-apache

# 1. Configuración base
RUN a2enmod rewrite headers proxy_http
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

# 2. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    wget git unzip libzip-dev libpq-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libgmp-dev libxslt-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd bcmath gmp zip xsl mbstring exif pdo_pgsql

# 3. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. Copiar solo los archivos esenciales primero
COPY composer.json composer.lock artisan /var/www/html/

# 5. Instalar dependencias sin scripts
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

# 6. Copiar el resto de la aplicación
COPY . /var/www/html/

# 7. Ejecutar scripts de instalación
RUN composer run-script post-autoload-dump

# 8. Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 9. Configurar Apache
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# 10. Configurar Xdebug
RUN pecl install xdebug-3.3.2 && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80
CMD ["apache2-foreground"]
