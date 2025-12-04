#!/usr/bin/env bash
set -e

echo "==> Running migrations + seed..."
php artisan migrate --force --seed

echo "==> Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting Apache..."
apache2-foreground
