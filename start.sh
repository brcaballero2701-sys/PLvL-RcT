#!/usr/bin/env bash
set -e

echo "=== Clearing caches ==="
php artisan optimize:clear || true

echo "=== Running migrations ==="
php artisan migrate --force --database=pgsql

echo "=== Linking storage ==="
php artisan storage:link || true

echo "=== Starting Apache ==="
exec apache2-foreground
