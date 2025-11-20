FROM php:8.3.21RC1-apache-bullseye

# 1. Configuración base
RUN a2enmod rewrite headers proxy_http
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

# 2. Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    wget git unzip libzip-dev libpq-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libgmp-dev libxslt-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd bcmath gmp zip xsl mbstring exif pdo pdo_pgsql

# 🔥 Aseguramos instalación de extensiones PDO antes de Composer
RUN docker-php-ext-enable pdo pdo_pgsql

# 3. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. Copiar archivos de Composer (para cachear dependencias)
COPY composer.json composer.lock ./

# 5. Instalar dependencias base sin ejecutar scripts
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

# 6. Copiar el resto de la aplicación
COPY . .

# 7. Instalar Socialite
RUN composer require laravel/socialite --no-scripts

# 8. Ejecutar scripts y limpiar cachés de Laravel
RUN composer dump-autoload && \
    php artisan package:discover --ansi || true && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear

# 9. Ejecutar scripts post-autoload
RUN composer run-script post-autoload-dump || true

# 10. Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 11. Configurar Apache para Laravel
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# 12. Instalar y configurar Xdebug
RUN pecl install xdebug-3.3.2 \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# 13. Variables de entorno necesarias para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APP_ENV=production

# 14. Exponer puerto (Render usa 10000 o dinámico)
EXPOSE 10000

# 15. Iniciar servidor Apache
CMD ["apache2-foreground"]
