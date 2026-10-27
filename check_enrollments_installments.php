<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Enrollment;
use App\Models\Installment;

echo "=== فحص التسجيلات والأقساط ===\n\n";

// جلب جميع التسجيلات
$enrollments = Enrollment::withoutGlobalScopes()->get();

echo "عدد التسجيلات: " . $enrollments->count() . "\n\n";

if ($enrollments->count() > 0) {
    foreach ($enrollments as $enrollment) {
        echo "التسجيل ID: {$enrollment->id}\n";
        echo "كود التسجيل: {$enrollment->enrollment_code}\n";
        echo "الطالب ID: {$enrollment->student_id}\n";
        echo "tenant_id: {$enrollment->tenant_id}\n";
        echo "------------------------\n";
    }
} else {
    echo "⚠️ لا توجد تسجيلات في قاعدة البيانات!\n";
}

echo "\n=== فحص الأقساط ===\n\n";
$installments = Installment::withoutGlobalScopes()->get();
echo "عدد الأقساط: " . $installments->count() . "\n\n";

foreach ($installments as $installment) {
    echo "القسط ID: {$installment->id}\n";
    echo "enrollment_id: {$installment->enrollment_id}\n";
    echo "المبلغ: {$installment->amount}\n";
    echo "الحالة: {$installment->status}\n";
    echo "------------------------\n";
}