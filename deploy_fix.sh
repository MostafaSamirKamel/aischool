#!/bin/bash

# Fix Google2FA Missing Package Error
echo "🔧 Fixing deployment issue..."

# Clear all caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Install dependencies
echo "📥 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Rebuild caches
echo "🔄 Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment fixed successfully!"
