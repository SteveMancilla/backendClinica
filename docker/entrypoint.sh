#!/usr/bin/env bash
set -e

echo "==> Preparando Laravel..."

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan storage:link || true

if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "==> Ejecutando migraciones..."
    php artisan migrate --force
fi

if [ "${RUN_DB_SEED}" = "true" ]; then
    echo "==> Ejecutando seeders..."
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Iniciando servidor..."
exec "$@"