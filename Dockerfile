FROM php:8.3.21RC1-apache-bullseye

# 1. Configuración base
RUN a2enmod rewrite headers proxy_http
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

# Instalar dependencias del sistema necesarias y extensiones PHP
RUN apt-get update && apt-get install -y \
    wget git unzip libzip-dev libpq-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libgmp-dev libxslt-dev libonig-dev \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd bcmath gmp zip xsl mbstring exif pdo pdo_pgsql
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 🔥 Aseguramos instalación de extensiones PDO antes de Composer
RUN docker-php-ext-enable pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 3. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# 4.Copiar archivos de Composer primero (cache)
COPY composer.json composer.lock ./ 

# 5. Instalar dependencias base sin ejecutar scripts
RUN composer install --no-interaction --no-dev --no-scripts --optimize-autoloader

# Crear carpetas necesarias y asignar permisos antes de Composer
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar un .env temporal
COPY .env.example .env

# 7. Instalar Socialite
RUN composer require laravel/socialite --no-scripts

# Instalar dependencias sin ejecutar scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copiar el resto del proyecto
COPY . .

# Ejecutar scripts post-install de Laravel
RUN composer dump-autoload \
    && php artisan package:discover --ansi || true \
    && php artisan key:generate --force || true \
    && php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

# Ajustar permisos finales
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
# Configurar Apache (RENDER LA NECESITA)
RUN apt-get update && apt-get install -y apache2 libapache2-mod-fcgid \
    && a2enmod rewrite proxy proxy_fcgi setenvif \
    && a2enconf php-fpm \
    && service apache2 restart

# Configurar DocumentRoot para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APP_ENV=production
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Exponer puerto (Render lo asigna)
EXPOSE 10000

# Iniciar Apache
CMD ["apache2-foreground"]
