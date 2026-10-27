#!/bin/bash

echo "========================================="
echo "   Fixing Nullsafe Operator Issues"
echo "   PHP 8.0 Compatibility Fix"
echo "========================================="
echo ""

# Go to project root
cd /home/almobtaker/public_html/aischool.almobtakeralaraby.com.eg

echo "1. Clearing all compiled views..."
php artisan view:clear

echo "2. Clearing application cache..."
php artisan cache:clear

echo "3. Clearing config cache..."
php artisan config:clear

echo "4. Clearing route cache..."
php artisan route:clear

echo "5. Clearing compiled classes..."
php artisan clear-compiled

echo "6. Clearing all optimization caches..."
php artisan optimize:clear

echo ""
echo "========================================="
echo "   Re-caching for Production"
echo "========================================="
echo ""

echo "7. Caching config..."
php artisan config:cache

echo "8. Caching routes..."
php artisan route:cache

echo "9. Caching views..."
php artisan view:cache

echo ""
echo "========================================="
echo "   ✅ Fix Complete!"
echo "========================================="
echo ""
echo "All caches cleared and re-cached."
echo "The nullsafe operator issues should now be resolved."
echo ""
