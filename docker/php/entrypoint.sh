#!/bin/sh
set -e

echo "==> Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo "    MySQL not ready, retrying in 3s..."
    sleep 3
done
echo "==> MySQL ready."

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

if [ "$APP_ENV" = "production" ]; then
    echo "==> Caching config & routes..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

echo "==> Linking storage..."
php artisan storage:link --quiet 2>/dev/null || true

echo "==> Starting PHP-FPM..."
exec "$@"
