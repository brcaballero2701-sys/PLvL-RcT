# Imagen base con PHP + Nginx + Opcache
FROM dunglas/frankenphp:1.1-php8.2

# Establecer directorio de trabajo
WORKDIR /app

# Instalar Node y npm
RUN apk add --no-cache nodejs npm

# Copiar el proyecto
COPY . .

# Instalar dependencias de backend
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias de frontend
RUN npm install

# Compilar React/Vite
RUN npm run build

# Cache de Laravel
RUN php artisan config:cache
RUN php artisan route:cache

# Exponer el puerto (Render usará $PORT)
EXPOSE 8080

# Iniciar Laravel con FrankenPHP
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
