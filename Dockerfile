FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias y extensiones PHP
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de Composer primero (cache)
COPY composer.json composer.lock ./ 

# Crear carpetas necesarias y asignar permisos antes de Composer
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar un .env temporal
COPY .env.example .env

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

# Configurar Apache (RENDER LA NECESITA)
RUN apt-get update && apt-get install -y apache2 libapache2-mod-fcgid \
    && a2enmod rewrite proxy proxy_fcgi setenvif \
    && a2enconf php-fpm \
    && service apache2 restart

# Configurar DocumentRoot para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Exponer puerto (Render lo asigna)
EXPOSE 10000

# Iniciar Apache
CMD ["apache2-foreground"]
