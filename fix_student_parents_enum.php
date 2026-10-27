<?php

/**
 * Fix Student Parents Relation Enum
 * تشغيل هذا الملف على السيرفر لإصلاح enum
 * 
 * Usage: php fix_student_parents_enum.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=========================================\n";
echo "تحديث enum لحقل relation في student_parents\n";
echo "Updating relation enum in student_parents\n";
echo "=========================================\n\n";

try {
    echo "1. التحقق من الحالة الحالية...\n";
    echo "   Checking current state...\n\n";
    
    $result = DB::select("SHOW COLUMNS FROM student_parents WHERE Field = 'relation'");
    
    if (!empty($result)) {
        echo "   الحالة الحالية / Current state:\n";
        echo "   Type: " . $result[0]->Type . "\n\n";
    }
    
    echo "2. تحديث enum...\n";
    echo "   Updating enum...\n\n";
    
    DB::statement("ALTER TABLE `student_parents` MODIFY `relation` ENUM('father', 'mother', 'guardian', 'brother', 'sister', 'uncle', 'aunt', 'other') NOT NULL");
    
    echo "   ✓ تم التحديث بنجاح!\n";
    echo "   ✓ Updated successfully!\n\n";
    
    echo "3. التحقق من النتيجة...\n";
    echo "   Verifying result...\n\n";
    
    $result = DB::select("SHOW COLUMNS FROM student_parents WHERE Field = 'relation'");
    
    if (!empty($result)) {
        echo "   الحالة الجديدة / New state:\n";
        echo "   Type: " . $result[0]->Type . "\n\n";
    }
    
    echo "=========================================\n";
    echo "✓ العملية اكتملت بنجاح!\n";
    echo "✓ Operation completed successfully!\n";
    echo "=========================================\n\n";
    
    echo "القيم المسموح بها الآن / Allowed values now:\n";
    echo "  - father (أب)\n";
    echo "  - mother (أم)\n";
    echo "  - guardian (ولي أمر)\n";
    echo "  - brother (أخ)\n";
    echo "  - sister (أخت)\n";
    echo "  - uncle (عم/خال)\n";
    echo "  - aunt (عمة/خالة)\n";
    echo "  - other (أخرى)\n\n";
    
} catch (Exception $e) {
    echo "✗ حدث خطأ / Error occurred:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
