#!/bin/bash

echo "🔧 Making group_id nullable in enrollments table..."

# Navigate to project directory
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

# Pull latest changes
echo "📥 Pulling latest code from git..."
git pull origin main

# Run the migration
echo "🗄️  Running migration..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Recache
echo "♻️  Re-caching..."
php artisan config:cache
php artisan route:cache

echo "✅ Done! group_id is now nullable in enrollments table."
echo "📝 You can now create enrollments without specifying a group."
