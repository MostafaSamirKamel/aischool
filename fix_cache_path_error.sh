#!/bin/bash

# Fix Laravel Cache Path Error
# Run this script on your server to fix the "Please provide a valid cache path" error

echo "========================================="
echo "Fixing Laravel Cache Path Error"
echo "========================================="

# Get the current directory
PROJECT_DIR=$(pwd)

echo "Project Directory: $PROJECT_DIR"
echo ""

# Step 1: Create all required storage directories
echo "1. Creating required storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "   ✓ Directories created"
echo ""

# Step 2: Create .gitignore files to preserve directory structure
echo "2. Creating .gitignore files..."
echo '*' > storage/framework/cache/.gitignore
echo '!.gitignore' >> storage/framework/cache/.gitignore
echo '*' > storage/framework/sessions/.gitignore
echo '!.gitignore' >> storage/framework/sessions/.gitignore
echo '*' > storage/framework/views/.gitignore
echo '!.gitignore' >> storage/framework/views/.gitignore

echo "   ✓ .gitignore files created"
echo ""

# Step 3: Set proper permissions
echo "3. Setting proper permissions..."

# Find the web server user (common options: www-data, apache, nginx, or cPanel username)
WEB_USER=""
if [ -n "$SUDO_USER" ]; then
    WEB_USER=$SUDO_USER
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    WEB_USER=$(whoami)
fi

echo "   Using web server user: $WEB_USER"

# Set ownership (uncomment if running with sudo)
# chown -R $WEB_USER:$WEB_USER storage bootstrap/cache

# Set directory permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set file permissions
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

echo "   ✓ Permissions set"
echo ""

# Step 4: Clear all caches
echo "4. Clearing Laravel caches..."
php artisan config:clear 2>/dev/null || echo "   ⚠ Config cache clear failed (might not exist)"
php artisan cache:clear 2>/dev/null || echo "   ⚠ Application cache clear failed (might not exist)"
php artisan view:clear 2>/dev/null || echo "   ⚠ View cache clear failed (might not exist)"
php artisan route:clear 2>/dev/null || echo "   ⚠ Route cache clear failed (might not exist)"
php artisan event:clear 2>/dev/null || echo "   ⚠ Event cache clear failed (might not exist)"

echo "   ✓ Caches cleared"
echo ""

# Step 5: Optimize for production (optional)
read -p "Do you want to optimize for production? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "5. Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "   ✓ Production optimization complete"
else
    echo "5. Skipping production optimization"
fi

echo ""
echo "========================================="
echo "✓ Cache path fix completed!"
echo "========================================="
echo ""
echo "Your Laravel application should now work correctly."
echo ""
echo "If you still have issues, check:"
echo "1. SELinux settings (if enabled): sudo setsebool -P httpd_unified 1"
echo "2. Verify .env file exists and has correct permissions"
echo "3. Check Apache/Nginx error logs for more details"
echo ""
