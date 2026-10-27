#!/bin/bash

echo "🔄 Pulling latest changes from Git..."
git pull origin main

echo "📦 Installing/updating dependencies..."
composer install --no-dev --optimize-autoloader

echo "🧹 Clearing all caches..."
php artisan optimize:clear

echo "⚡ Optimizing for production..."
php artisan optimize
php artisan filament:optimize

echo "✅ Deployment complete!"
