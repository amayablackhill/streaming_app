#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8080}"
export PORT

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

mkdir -p \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  storage/app/public \
  storage/app/public/videos/source \
  storage/app/public/videos/hls \
  bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/web.conf
