FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias y extensiones PHP
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos de Composer primero
COPY composer.json composer.lock ./

# Crear carpetas necesarias y asignar permisos antes de Composer
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar un .env temporal para evitar errores de artisan
COPY .env.example .env

# Instalar dependencias de Composer sin ejecutar scripts de artisan
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

# Exponer puerto dinámico (Render usa $PORT)
EXPOSE 10000

# Iniciar servidor Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT:-8000}"]
