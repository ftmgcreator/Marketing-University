#!/bin/sh
set -e

cd /var/www/html

# Ensure runtime dirs exist (when mounted via volumes they may be empty)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs bootstrap/cache database

# Ensure SQLite database file exists
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

chown -R www-data:www-data storage bootstrap/cache database
chmod -R ug+rwx storage bootstrap/cache database

# Storage symlink (idempotent)
php artisan storage:link --force >/dev/null 2>&1 || true

# Run migrations
php artisan migrate --force --no-interaction || true

# Cache config/routes/views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
