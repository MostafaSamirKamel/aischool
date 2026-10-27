<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إصلاح foreign key يدوياً ===\n\n";

try {
    // فحص الـ foreign keys الحالية
    echo "فحص foreign keys الحالية في جدول groups...\n";
    
    $foreignKeys = DB::select("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'groups'
        AND REFERENCED_TABLE_NAME IS NOT NULL
        AND COLUMN_NAME = 'instructor_id'
    ");
    
    if (count($foreignKeys) > 0) {
        echo "Foreign keys موجودة:\n";
        foreach ($foreignKeys as $fk) {
            echo "- {$fk->CONSTRAINT_NAME}: {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
            
            // حذف foreign key إذا كان يشير لجدول users
            if ($fk->REFERENCED_TABLE_NAME === 'users') {
                echo "حذف foreign key القديم: {$fk->CONSTRAINT_NAME}\n";
                DB::statement("ALTER TABLE groups DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }
        }
    } else {
        echo "لا توجد foreign keys موجودة للعمود instructor_id\n";
    }
    
    // إضافة foreign key جديد يشير لجدول instructors
    echo "\nإضافة foreign key جديد...\n";
    DB::statement("
        ALTER TABLE groups 
        ADD CONSTRAINT groups_instructor_id_foreign 
        FOREIGN KEY (instructor_id) 
        REFERENCES instructors(id) 
        ON DELETE SET NULL
    ");
    
    echo "✅ تم إصلاح foreign key بنجاح!\n";
    echo "groups.instructor_id يشير الآن إلى instructors.id\n";
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    
    // إذا كان foreign key موجود بالفعل
    if (str_contains($e->getMessage(), 'Duplicate key')) {
        echo "✅ Foreign key موجود بالفعل - لا حاجة لإضافته\n";
    }
}

echo "\nالآن يمكنك تشغيل:\n";
echo "1. حذف migration المشكلة: rm database/migrations/2026_01_11_164218_fix_groups_instructor_foreign_key.php\n";
echo "2. تشغيل باقي migrations: php artisan migrate\n";
