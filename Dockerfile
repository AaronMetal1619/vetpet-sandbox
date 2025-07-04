FROM php:8.2-apache

# Habilitar módulos de Apache
RUN a2enmod rewrite headers proxy_http

# Instalar extensiones necesarias + PostgreSQL
RUN apt-get update && apt-get install -y \
    wget gnupg git unzip zip \
    libzip-dev libxslt-dev libpng-dev libjpeg-dev \
    libgmp-dev libfreetype6-dev libonig-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd bcmath gmp zip xsl mbstring exif pdo_pgsql

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copiar código Laravel al contenedor
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar dependencias de Laravel
RUN composer install --no-interaction --optimize-autoloader

# Asignar permisos a carpetas necesarias
RUN chown -R www-data:www-data storage bootstrap/cache

# Configurar Apache para Laravel
COPY laravel.conf /etc/apache2/sites-available/laravel.conf
RUN a2dissite 000-default.conf && a2ensite laravel.conf

# Instalar y configurar Xdebug
RUN pecl install xdebug-3.3.2 && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80
CMD ["apache2-foreground"]
