#!/usr/bin/env sh
set -eu

cd /var/www/html

export PORT="${PORT:-10000}"

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

chown -R www-data:www-data storage bootstrap/cache database

envsubst '${PORT}' < /etc/nginx/templates/render.conf.template > /etc/nginx/http.d/default.conf

if [ -z "${APP_KEY:-}" ]; then
    echo "WARNING: APP_KEY is not set. Set APP_KEY in Render environment variables for stable sessions and encryption."
fi

php artisan package:discover --ansi || true
php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force || echo "WARNING: migrations failed or database is unavailable."
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
