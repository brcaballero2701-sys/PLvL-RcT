FROM php:8.2-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Habilitar mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copiar el proyecto
COPY . .

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Cache de Laravel (si falla no detiene el build)
RUN php artisan config:cache || true
RUN php artisan route:cache || true

EXPOSE 8080

CMD ["apache2-foreground"]
