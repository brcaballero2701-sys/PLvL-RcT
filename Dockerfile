FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    zip unzip curl git \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Render usa el puerto 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Copiar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Cache (si falla, continuar)
RUN php artisan config:cache || true
RUN php artisan route:cache || true

EXPOSE 8080

CMD ["apache2-foreground"]
