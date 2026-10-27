<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إصلاح سجلات المعلمين ===\n\n";

// جلب جميع المستخدمين الذين لديهم دور instructor ولكن بدون سجل
$usersWithInstructorRole = \App\Models\User::role('instructor')
    ->doesntHave('instructor')
    ->get();

if ($usersWithInstructorRole->isEmpty()) {
    echo "✅ لا توجد مشاكل! جميع المستخدمين الذين لديهم دور instructor لديهم سجلات.\n";
    exit;
}

echo "تم العثور على {$usersWithInstructorRole->count()} مستخدم يحتاج إلى إصلاح:\n\n";

foreach ($usersWithInstructorRole as $user) {
    echo "معالجة: {$user->name} ({$user->email})\n";
    
    // التحقق من tenant_id
    if (!$user->tenant_id) {
        echo "  ⚠️ المستخدم ليس لديه tenant_id\n";
        // محاولة الحصول على أول tenant متاح
        $firstTenant = \App\Models\Tenant::first();
        if ($firstTenant) {
            $user->tenant_id = $firstTenant->id;
            $user->save();
            echo "  ✅ تم تعيين tenant_id = {$firstTenant->id} ({$firstTenant->name})\n";
        } else {
            echo "  ❌ لا يوجد tenants في النظام! يجب إنشاء tenant أولاً\n";
            continue;
        }
    }
    
    try {
        // توليد employee_code فريد
        $employeeCode = 'INS-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
        
        // إنشاء سجل instructor جديد
        $instructor = \App\Models\Instructor::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'employee_code' => $employeeCode,
            'phone' => $user->phone ?? null,
            'specialization' => 'عام', // تخصص افتراضي
            'bio' => null,
            'qualification' => null,
            'experience_years' => 0,
            'hire_date' => now(),
            'status' => 'active',
        ]);
        
        echo "  ✅ تم إنشاء سجل Instructor بنجاح (ID: {$instructor->id}, Code: {$employeeCode})\n";
    } catch (\Exception $e) {
        echo "  ❌ خطأ: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== انتهى الإصلاح ===\n";
echo "\nيمكنك الآن الوصول إلى صفحة إنشاء رسائل المجموعات بدون مشاكل!\n";
