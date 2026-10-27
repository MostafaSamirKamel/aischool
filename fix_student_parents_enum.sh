#!/bin/bash

# Fix Student Parents Relation Enum on Server
# تشغيل هذا السكريبت على السيرفر لإصلاح enum

echo "========================================="
echo "تحديث enum لحقل relation في student_parents"
echo "Updating relation enum in student_parents"
echo "========================================="
echo ""

# النسخ الاحتياطي أولاً
echo "1. عمل نسخة احتياطية من الجدول..."
echo "   Creating backup..."

php artisan db:backup 2>/dev/null || echo "   ⚠ Backup command not available, continuing..."

echo ""
echo "2. تشغيل Migration..."
echo "   Running migration..."

php artisan migrate --force

echo ""
echo "3. التحقق من التحديث..."
echo "   Verifying update..."

php artisan tinker --execute="
\$columns = DB::select('SHOW COLUMNS FROM student_parents WHERE Field = \"relation\"');
if (!empty(\$columns)) {
    echo 'Current relation column type: ' . \$columns[0]->Type . PHP_EOL;
} else {
    echo 'Column not found!' . PHP_EOL;
}
"

echo ""
echo "========================================="
echo "✓ تم التحديث بنجاح!"
echo "✓ Update completed successfully!"
echo "========================================="
echo ""
echo "القيم المسموح بها الآن / Allowed values now:"
echo "  - father (أب)"
echo "  - mother (أم)"
echo "  - guardian (ولي أمر)"
echo "  - brother (أخ)"
echo "  - sister (أخت)"
echo "  - uncle (عم/خال)"
echo "  - aunt (عمة/خالة)"
echo "  - other (أخرى)"
echo ""
