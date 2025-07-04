# Se utiliza una imagen de PHP 8.2 con apache
FROM php:8.2-apache

# Habilitar los modulos de apache
RUN a2enmod rewrite headers
RUN a2enmod proxy_http

# Instalar las extensiones de PHP necesarias
RUN apt-get update && apt-get install -y wget gnupg git unzip zip libzip-dev libxslt-dev libpng-dev libjpeg.dev libgmp-dev libfreetype6-dev libonig-dev

#Instalar dependecias de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd\
 && docker-php-ext-install bcmath gmp zip xsl gd pdo_mysql mbstring zip exif

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.3.10

# Habilitar composer allow superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copiar nuestras llaves SSH del directorio de ssh al contenedor
COPY id_rsa /root/.ssh/id_rsa
COPY id_rsa.pub /root/.ssh/id_rsa.pub 

# Configurar permisos y SSH
RUN chmod 600 /root/.ssh/id_rsa \
 && chmod 644 /root/.ssh/id_rsa.pub \
 && mkdir -p /root/.ssh \
 && ssh-keyscan github.com >> /root/.ssh/known_hosts

# Clonar directamente la rama deseada
RUN git clone --branch Miguel git@github.com:MiguelAngelMartinPuga/Backend.git /var/www/Backend

# Establecer directorio de trabajo
WORKDIR /var/www/Backend

# Instalar dependencias de Composer
RUN composer install --no-interaction --optimize-autoloader

#Configurar laravel.conf para Apache
COPY laravel.conf /etc/apache2/sites-available/laravel.conf

#Habilitar el sitio de laravel
RUN a2ensite laravel.conf

# Install Xdebug 3.3.2
RUN pecl install xdebug-3.3.2 \
    && docker-php-ext-enable xdebug

# Configure Xdebug
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#Exportar el puerto y usar 80
EXPOSE 80

# Ejecutar el comando de inicio de apache
CMD ["apache2-foreground"]
