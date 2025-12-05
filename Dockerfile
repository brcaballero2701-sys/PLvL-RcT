FROM php:8.4-apache

# Instalar extensiones necesarias y Node.js
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev libsodium-dev nodejs npm \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip sodium

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache para servir desde public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copiar el proyecto
COPY . .

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias de Node.js y compilar assets
RUN npm install && npm run build

# Generar clave de aplicación si no existe
RUN if [ ! -f .env ]; then cp .env.example .env; fi
RUN php artisan key:generate --force || true

# Crear directorios necesarios con permisos correctos
RUN mkdir -p storage/logs storage/app storage/framework/cache storage/framework/sessions storage/framework/views && \
    mkdir -p bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache .env && \
    touch storage/logs/laravel.log && \
    chmod 666 storage/logs/laravel.log

# Crear base de datos SQLite con permisos correctos
RUN mkdir -p database && \
    touch database/database.sqlite && \
    chmod 777 database && \
    chmod 666 database/database.sqlite && \
    chown -R www-data:www-data database

# Ejecutar migraciones para crear las tablas
RUN php artisan migrate --force || true

# Configurar APP_URL y forzar HTTPS en producción
RUN sed -i "s|APP_URL=.*|APP_URL=https://senacrewjdk.onrender.com|g" .env && \
    sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env && \
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env

# Cache de Laravel (si falla no detiene el build)
RUN php artisan config:cache || true
RUN php artisan route:cache || true

# Limpiar npm cache para reducir tamaño de la imagen
RUN npm cache clean --force && rm -rf node_modules

EXPOSE 8080

CMD ["apache2-foreground"]