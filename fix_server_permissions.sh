#!/bin/bash

# Fix Laravel Storage Permissions and Create Missing Directories
# Run this on your production server: bash fix_server_permissions.sh

echo "===== Fixing Laravel Permissions for AI School ====="
echo ""

# Navigate to project directory
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg || exit

echo "1. Creating required directories..."

# Create all required storage directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

echo "✓ Directories created"
echo ""

echo "2. Setting ownership..."

# Set ownership - replace 'almobtaker' with your cPanel username
chown -R almobtaker:almobtaker storage
chown -R almobtaker:almobtaker bootstrap/cache

echo "✓ Ownership set"
echo ""

echo "3. Setting directory permissions..."

# Set directory permissions (755 for directories, 644 for files)
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;

find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Make sure these directories are writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✓ Permissions set"
echo ""

echo "4. Clearing Laravel caches..."

# Clear all Laravel caches
php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan view:clear 2>/dev/null
php artisan route:clear 2>/dev/null
php artisan optimize:clear 2>/dev/null

echo "✓ Caches cleared"
echo ""

echo "===== Done! Your application should work now. ====="
echo ""
echo "If you still have issues, try running:"
echo "  php artisan storage:link"
echo "  composer dump-autoload"
