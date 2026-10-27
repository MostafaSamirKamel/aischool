#!/bin/bash

echo "🚀 Deploying to server..."

# Navigate to project directory
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# Pull latest changes
echo "📥 Pulling latest code from git..."
git pull origin main

# Install/update npm dependencies
echo "📦 Installing npm dependencies..."
npm install

# Build assets
echo "🔨 Building assets..."
npm run build

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

# Recache for production
echo "♻️  Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
echo "🔒 Fixing permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment complete!"
echo "🌐 Site: https://aischool.almobtakeralaraby.com.eg"
