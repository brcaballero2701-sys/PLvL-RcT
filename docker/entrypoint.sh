#!/usr/bin/env sh
set -e

echo "== Fixing Laravel permissions =="

mkdir -p storage/framework/{cache,data,sessions,views}
mkdir -p bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "== Starting Apache =="
exec apache2-foreground
