#!/bin/bash
set -e

echo 'Ejecutando migraciones de base de datos...'
php artisan migrate --force

echo 'Sembrando datos iniciales...'
php artisan db:seed --force

echo 'Limpiando cache...'
php artisan cache:clear
php artisan config:cache
php artisan route:cache

echo 'Completado!'
