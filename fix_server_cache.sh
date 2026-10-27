#!/bin/bash

echo "🧹 Clearing server cache..."

# Clear all caches
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Dump composer autoload
echo "🔄 Regenerating autoload files..."
composer dump-autoload -o

# Recache for production
echo "♻️  Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "✅ Cache cleared and regenerated!"
