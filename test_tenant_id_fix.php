<?php

/**
 * Test Tenant ID Auto-Assignment
 * 
 * هذا الملف لاختبار أن جميع النماذج تأخذ tenant_id تلقائياً
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// قائمة النماذج التي يجب أن تحتوي على BelongsToBranch trait
$modelsWithTenantId = [
    'ExpenseCategory',
    'RevenueCategory',
    'Expense',
    'Revenue',
    'Department',
    'Profession',
    'Occupation',
    'Nationality',
    'StudentPayment',
    'Setting',
    // النماذج التي تحتوي على BelongsToBranch بالفعل
    'Assignment',
    'AttendanceSession',
    'Attendance',
    'AssignmentSubmission',
    'Enrollment',
    'Exam',
    'ExamAttempt',
    'Grade',
    'Payment',
    'StudentParent',
    'Track',
    'Group',
    'Classroom',
    'Course',
];

echo "\n=== فحص النماذج التي تحتوي على tenant_id ===\n\n";

$success = 0;
$failed = 0;

foreach ($modelsWithTenantId as $modelName) {
    $className = "App\\Models\\{$modelName}";
    
    if (!class_exists($className)) {
        echo "❌ {$modelName} - الكلاس غير موجود\n";
        $failed++;
        continue;
    }
    
    $traits = class_uses_recursive($className);
    
    if (in_array('App\\Models\\Concerns\\BelongsToBranch', $traits)) {
        echo "✅ {$modelName} - يستخدم BelongsToBranch trait\n";
        $success++;
    } else {
        echo "⚠️  {$modelName} - لا يستخدم BelongsToBranch trait\n";
        $failed++;
    }
}

echo "\n=== النتيجة ===\n";
echo "نجح: {$success}\n";
echo "فشل: {$failed}\n";

// فحص BelongsToBranch trait
echo "\n=== فحص BelongsToBranch trait ===\n";
$traitPath = __DIR__ . '/app/Models/Concerns/BelongsToBranch.php';
if (file_exists($traitPath)) {
    $content = file_get_contents($traitPath);
    if (strpos($content, 'static::creating') !== false) {
        echo "✅ BelongsToBranch trait يحتوي على creating event\n";
    } else {
        echo "❌ BelongsToBranch trait لا يحتوي على creating event\n";
    }
    
    if (strpos($content, 'tenant_id') !== false) {
        echo "✅ BelongsToBranch trait يتعامل مع tenant_id\n";
    } else {
        echo "❌ BelongsToBranch trait لا يتعامل مع tenant_id\n";
    }
} else {
    echo "❌ BelongsToBranch trait غير موجود\n";
}

echo "\n=== اختبار كامل ===\n";
