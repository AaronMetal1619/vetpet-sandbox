FROM php:8.3.21RC1-apache-bullseye

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

# 4. Copiar solo los archivos esenciales primero (optimiza caché)
COPY composer.json composer.lock artisan ./

# 5. Instalar dependencias sin dev ni scripts pesados
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

# 6. Copiar el resto de la aplicación
COPY . .

# 7. Ejecutar scripts de instalación de Laravel
RUN composer run-script post-autoload-dump || true

# 8. Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 9. Configurar Apache para Laravel
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# 10. Instalar y configurar Xdebug
RUN pecl install xdebug-3.3.2 \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# 11. Variables de entorno necesarias para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APP_ENV=production

# Exponer el puerto (Render requiere 10000 o dinámico)
EXPOSE 10000

# Iniciar servidor Apache
CMD ["apache2-foreground"]
