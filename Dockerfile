# Imagen base con PHP y FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos de Composer primero (para aprovechar cache)
COPY composer.json composer.lock ./

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto de la aplicación
COPY . .

# Asignar permisos correctos a storage y cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache || true

# Generar clave de aplicación (si no existe)
RUN php artisan key:generate --force || true

# Exponer el puerto que Render asignará dinámicamente
EXPOSE 10000

# Comando de inicio — Render usa variable $PORT automáticamente
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"]
