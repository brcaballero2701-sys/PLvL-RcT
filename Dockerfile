FROM php:8.3-apache

# -----------------------------
# 1) Dependencias del sistema + extensiones PHP
# -----------------------------
RUN apt-get update && apt-get install -y \
    zip unzip curl git \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libxml2-dev libzip-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# 2) Habilitar mod_rewrite
# -----------------------------
RUN a2enmod rewrite

# -----------------------------
# 3) Render usa puerto 8080
# -----------------------------
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# -----------------------------
# 4) Apuntar Apache a /public (FIX 403)
# -----------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Asegurar que .htaccess funcione en public/
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# -----------------------------
# 5) Copiar proyecto
# -----------------------------
WORKDIR /var/www/html
COPY . .

# -----------------------------
# 6) Copiar Composer
# -----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------
# 7) Instalar dependencias Laravel
# -----------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -----------------------------
# 8) Permisos
# -----------------------------
RUN chown -R www-data:www-data storage bootstrap/cache

# -----------------------------
# 9) Cache (si falla, continuar)
# -----------------------------
RUN php artisan config:cache || true
RUN php artisan route:cache || true

EXPOSE 8080

CMD ["apache2-foreground"]


