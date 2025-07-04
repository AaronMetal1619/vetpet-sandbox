FROM php:8.2-apache

# Habilita módulos de Apache necesarios
RUN a2enmod rewrite headers proxy_http

# Instala dependencias necesarias, incluyendo libpq-dev para PostgreSQL
RUN apt-get update && apt-get install -y \
    wget gnupg git unzip zip \
    libzip-dev libxslt-dev libpng-dev libjpeg-dev \
    libgmp-dev libfreetype6-dev libonig-dev \
    libpq-dev  # ← NECESARIO para pdo_pgsql

# Configura e instala extensiones PHP necesarias, solo pdo_pgsql
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    gd bcmath gmp zip xsl mbstring exif \
    pdo_pgsql  # ← Solo PostgreSQL

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copia tu código al contenedor y establece el directorio de trabajo
COPY . /var/www/Backend
WORKDIR /var/www/Backend

# Instala dependencias de Laravel
RUN composer install --no-interaction --optimize-autoloader

# Ajusta permisos (opcional pero recomendable)
RUN chown -R www-data:www-data /var/www/Backend/storage /var/www/Backend/bootstrap/cache

# Configura Apache para Laravel
COPY laravel.conf /etc/apache2/sites-available/laravel.conf
RUN a2dissite 000-default.conf && a2ensite laravel.conf

# Instala y configura Xdebug (opcional, solo si haces debug local)
RUN pecl install xdebug-3.3.2 && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80
CMD ["apache2-foreground"]
