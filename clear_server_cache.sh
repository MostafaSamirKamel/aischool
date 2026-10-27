#!/bin/bash

# Clear all Laravel caches on server
echo "Clearing Laravel caches..."

# Clear compiled views (this is the main issue)
php artisan view:clear

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear compiled classes
php artisan clear-compiled

# Optimize for production
php artisan optimize:clear

echo "All caches cleared successfully!"
echo ""
echo "Now re-caching for production..."

# Re-cache config
php artisan config:cache

# Re-cache routes
php artisan route:cache

# Re-cache views
php artisan view:cache

echo "Production optimization complete!"
