#!/usr/bin/env sh
set -e

# Default port to 8080 if not provided by platform (Railway dynamically sets PORT)
export PORT="${PORT:-8080}"

# Substitute PORT in Nginx configuration template
if [ -f /etc/nginx/templates/default.conf.template ]; then
    envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf
fi

# Ensure storage directories exist
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/app/private \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Set permissions for web runtime user
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure storage symlink exists
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --force || true
fi

# Run database migrations if explicitly enabled
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --isolated || php artisan migrate --force
fi

# Framework optimization caches (run when APP_ENV is production and APP_KEY is provided)
if [ "${APP_ENV}" = "production" ] && [ -n "${APP_KEY}" ]; then
    echo "Optimizing framework caches for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"
