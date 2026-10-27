<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إنشاء Tenant وإصلاح المعلمين ===\n\n";

// 1. إنشاء tenant إذا لم يكن موجود
$tenant = \App\Models\Tenant::first();

if (!$tenant) {
    echo "إنشاء Tenant جديد...\n";
    $tenant = \App\Models\Tenant::create([
        'name' => 'المركز الرئيسي',
        'slug' => 'main-center',
        'email' => 'info@aischool.com',
        'phone' => '01000000000',
        'address' => 'المقر الرئيسي',
        'status' => 'active',
    ]);
    echo "✅ تم إنشاء Tenant: {$tenant->name} (ID: {$tenant->id})\n\n";
} else {
    echo "✅ Tenant موجود: {$tenant->name} (ID: {$tenant->id})\n\n";
}

// 2. إصلاح المستخدمين الذين لديهم دور instructor
$usersWithInstructorRole = \App\Models\User::role('instructor')
    ->doesntHave('instructor')
    ->get();

if ($usersWithInstructorRole->isEmpty()) {
    echo "✅ لا توجد مشاكل! جميع المستخدمين محدثين.\n";
    exit;
}

echo "تم العثور على {$usersWithInstructorRole->count()} مستخدم يحتاج إلى إصلاح:\n\n";

foreach ($usersWithInstructorRole as $user) {
    echo "معالجة: {$user->name} ({$user->email})\n";
    
    // تحديث tenant_id للمستخدم
    if (!$user->tenant_id) {
        $user->tenant_id = $tenant->id;
        $user->save();
        echo "  ✅ تم تعيين tenant_id = {$tenant->id}\n";
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
            'specialization' => 'عام',
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

echo "=== انتهى الإصلاح بنجاح! ===\n";
echo "\n✅ يمكنك الآن الوصول إلى /instructor/group-messages/create بدون مشاكل!\n";
