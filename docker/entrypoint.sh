#!/bin/sh
set -e

cd /var/www/html

# Ensure runtime dirs exist (when mounted via volumes they may be empty)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

# Wait for PostgreSQL to accept connections
if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ]; then
    : "${DB_HOST:=db}"
    : "${DB_PORT:=5432}"
    : "${DB_USERNAME:=marketing}"
    : "${DB_DATABASE:=marketing_university}"
    echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
    for i in $(seq 1 60); do
        if PGPASSWORD="${DB_PASSWORD}" pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" >/dev/null 2>&1; then
            echo "PostgreSQL is ready."
            break
        fi
        sleep 1
    done
fi

# Storage symlink (idempotent)
php artisan storage:link --force >/dev/null 2>&1 || true

# Run migrations
php artisan migrate --force --no-interaction || true

# Cache config/routes/views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
