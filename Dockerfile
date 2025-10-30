# Imagen base con PHP y FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar composer.json y composer.lock (para cachear dependencias)
COPY composer.json composer.lock ./

# Crear directorios requeridos antes de composer install
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar un .env de ejemplo (necesario para artisan durante composer install)
COPY .env.example .env

# Instalar dependencias PHP de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto de la aplicación
COPY . .

# Generar APP_KEY y limpiar caches
RUN php artisan key:generate --force || true \
    && php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

# Asignar permisos finales
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Exponer el puerto (Render asigna $PORT automáticamente)
EXPOSE 10000

# Iniciar el servidor de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"]
