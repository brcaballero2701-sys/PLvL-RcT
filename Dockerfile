FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y \
    zip unzip curl git \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Cambiar el puerto de Apache a 8080 (Render usa este puerto)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copiar el código del proyecto
COPY . .

# Copiar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ajustar permisos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Cache de Laravel (si falla, no detener el build)
RUN php artisan config:cache || true
RUN php artisan route:cache || true

EXPOSE 8080

CMD ["apache2-foreground"]

