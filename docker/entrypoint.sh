#!/bin/sh
set -e

cd /app

# Render inyecta DATABASE_URL; Laravel usa DB_URL
if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
  export DB_URL="$DATABASE_URL"
fi

if [ -z "$APP_KEY" ]; then
  echo "ERROR: Define APP_KEY en Render (php artisan key:generate --show en local)."
  exit 1
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

php artisan config:clear --no-ansi
php artisan migrate --force --no-interaction --no-ansi

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
  php artisan db:seed --force --no-interaction --no-ansi
fi

php artisan config:cache --no-ansi
php artisan route:cache --no-ansi
php artisan view:cache --no-ansi 2>/dev/null || true

php artisan storage:link --force --no-interaction --no-ansi 2>/dev/null || true

exec "$@"
