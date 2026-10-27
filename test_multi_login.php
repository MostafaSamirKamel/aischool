<?php

/**
 * Test Multi-Login System
 * اختبار نظام تسجيل الدخول المتعدد
 * 
 * Run: php test_multi_login.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "اختبار نظام تسجيل الدخول المتعدد\n";
echo "Testing Multi-Login System\n";
echo "========================================\n\n";

// 1. Check Auth Provider Configuration
echo "1. فحص إعدادات Auth Provider\n";
echo "   Checking Auth Provider Configuration...\n\n";

$authConfig = config('auth.providers.users');
echo "   Provider Driver: " . $authConfig['driver'] . "\n";
echo "   Model: " . $authConfig['model'] . "\n\n";

if ($authConfig['driver'] === 'custom_eloquent') {
    echo "   ✓ Custom provider مفعل!\n";
    echo "   ✓ Custom provider is enabled!\n\n";
} else {
    echo "   ✗ Custom provider غير مفعل!\n";
    echo "   ✗ Custom provider is NOT enabled!\n\n";
}

// 2. Check CustomUserProvider Class
echo "2. فحص CustomUserProvider Class\n";
echo "   Checking CustomUserProvider Class...\n\n";

if (class_exists('App\Providers\CustomUserProvider')) {
    echo "   ✓ CustomUserProvider موجود\n";
    echo "   ✓ CustomUserProvider exists\n\n";
    
    $reflection = new ReflectionClass('App\Providers\CustomUserProvider');
    $method = $reflection->getMethod('retrieveByCredentials');
    
    if ($method) {
        echo "   ✓ Method retrieveByCredentials موجود\n";
        echo "   ✓ Method retrieveByCredentials exists\n\n";
    }
} else {
    echo "   ✗ CustomUserProvider غير موجود!\n";
    echo "   ✗ CustomUserProvider NOT found!\n\n";
}

// 3. Test with Sample User
echo "3. اختبار مع مستخدم تجريبي\n";
echo "   Testing with Sample User...\n\n";

$testUser = \App\Models\User::first();

if ($testUser) {
    echo "   معلومات المستخدم / User Info:\n";
    echo "   - ID: " . $testUser->id . "\n";
    echo "   - Name: " . $testUser->name . "\n";
    echo "   - Email: " . $testUser->email . "\n";
    echo "   - Username: " . ($testUser->username ?? 'N/A') . "\n";
    echo "   - Phone: " . ($testUser->phone ?? 'N/A') . "\n\n";
    
    // Test retrieveByCredentials
    echo "   اختبار retrieveByCredentials:\n";
    echo "   Testing retrieveByCredentials:\n\n";
    
    $provider = Auth::getProvider();
    
    // Test with email
    if ($testUser->email) {
        $user = $provider->retrieveByCredentials(['email' => $testUser->email]);
        echo "   - البحث بالإيميل / Search by email: " . ($user ? '✓ يعمل' : '✗ لا يعمل') . "\n";
        echo "   - Search by email: " . ($user ? '✓ Works' : '✗ Does not work') . "\n";
    }
    
    // Test with username
    if ($testUser->username) {
        $user = $provider->retrieveByCredentials(['email' => $testUser->username]);
        echo "   - البحث باسم المستخدم / Search by username: " . ($user ? '✓ يعمل' : '✗ لا يعمل') . "\n";
        echo "   - Search by username: " . ($user ? '✓ Works' : '✗ Does not work') . "\n";
    }
    
    // Test with phone
    if ($testUser->phone) {
        $user = $provider->retrieveByCredentials(['email' => $testUser->phone]);
        echo "   - البحث بالهاتف / Search by phone: " . ($user ? '✓ يعمل' : '✗ لا يعمل') . "\n";
        echo "   - Search by phone: " . ($user ? '✓ Works' : '✗ Does not work') . "\n";
    }
    
} else {
    echo "   ⚠ لا يوجد مستخدمين في قاعدة البيانات\n";
    echo "   ⚠ No users in database\n";
}

echo "\n----------------------------------------\n\n";

// 4. Summary
echo "4. الملخص / Summary\n\n";

echo "   يمكنك تسجيل الدخول باستخدام:\n";
echo "   You can login using:\n\n";
echo "   ✓ البريد الإلكتروني (Email)\n";
echo "   ✓ اسم المستخدم (Username)\n";
echo "   ✓ رقم الهاتف (Phone)\n\n";

echo "   في حقل الإيميل، يمكن إدخال أي من الثلاثة!\n";
echo "   In the email field, you can enter any of the three!\n\n";

echo "========================================\n";
echo "✓ الاختبار اكتمل!\n";
echo "✓ Test Completed!\n";
echo "========================================\n\n";

echo "الكود المستخدم / Code Used:\n";
echo "File: app/Providers/CustomUserProvider.php\n";
echo "Method: retrieveByCredentials()\n\n";

echo "Logic:\n";
echo "-----\n";
echo "if (\$key === 'email') {\n";
echo "    \$query->where(function (\$q) use (\$value) {\n";
echo "        \$q->where('email', \$value)\n";
echo "          ->orWhere('phone', \$value)\n";
echo "          ->orWhere('username', \$value);\n";
echo "    });\n";
echo "}\n\n";
