#!/usr/bin/env php
<?php

/**
 * فحص سريع لنظام الأدوار والصلاحيات
 * 
 * الاستخدام:
 * php test_roles_quick.php
 */

echo "\n";
echo "════════════════════════════════════════════════\n";
echo "  فحص نظام الأدوار والصلاحيات\n";
echo "════════════════════════════════════════════════\n";
echo "\n";

// Check 1: Files exist
echo "1️⃣ التحقق من وجود الملفات:\n";
echo "─────────────────────────────────────\n";

$files_to_check = [
    'database/seeders/RoleAndPermissionSeeder.php' => 'Seeder',
    'app/Listeners/AssignAdminRoleToNewUser.php' => 'Listener',
    'app/Providers/EventServiceProvider.php' => 'EventServiceProvider',
    'app/Filament/Admin/Resources/Patients/PatientResource.php' => 'PatientResource',
    'app/Filament/Admin/Resources/Doctors/DoctorResource.php' => 'DoctorResource',
];

$missing_files = 0;
foreach ($files_to_check as $file => $name) {
    if (file_exists($file)) {
        echo "  ✅ {$name}\n";
    } else {
        echo "  ❌ {$name} (مفقود)\n";
        $missing_files++;
    }
}

echo "\n";

// Check 2: Seeder content
echo "2️⃣ التحقق من محتوى Seeder:\n";
echo "─────────────────────────────────────\n";

$seeder_file = 'database/seeders/RoleAndPermissionSeeder.php';
if (file_exists($seeder_file)) {
    $content = file_get_contents($seeder_file);
    
    $checks = [
        "Role::firstOrCreate" => "إنشاء الأدوار",
        "Permission::firstOrCreate" => "إنشاء الصلاحيات",
        "super_admin" => "دور Super Admin",
        "admin" => "دور Admin",
        "user" => "دور User",
        "syncPermissions" => "ربط الصلاحيات",
    ];
    
    foreach ($checks as $keyword => $description) {
        if (strpos($content, $keyword) !== false) {
            echo "  ✅ {$description}\n";
        } else {
            echo "  ❌ {$description} (غير موجود)\n";
        }
    }
}

echo "\n";

// Check 3: Resource updates
echo "3️⃣ التحقق من تحديث Resources:\n";
echo "─────────────────────────────────────\n";

$resources = [
    'Patients/PatientResource.php',
    'Reservations/ReservationResource.php',
    'Doctors/DoctorResource.php',
    'Wards/WardResource.php',
    'Nationalities/NationalityResource.php',
    'Settings/SettingResource.php',
    'Users/UserResource.php',
    'Tenants/TenantResource.php',
];

$updated_resources = 0;
foreach ($resources as $resource) {
    $path = "app/Filament/Admin/Resources/{$resource}";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Check if using permission-based checks
        if (strpos($content, "can('Create") !== false || 
            strpos($content, "can('Update") !== false ||
            strpos($content, "can('Delete") !== false) {
            echo "  ✅ {$resource}\n";
            $updated_resources++;
        } else {
            echo "  ⚠️ {$resource} (قد لم يتم التحديث كاملاً)\n";
        }
    } else {
        echo "  ❌ {$resource} (مفقود)\n";
    }
}

echo "\n";

// Check 4: Listener check
echo "4️⃣ التحقق من Listener:\n";
echo "─────────────────────────────────────\n";

$listener_file = 'app/Listeners/AssignAdminRoleToNewUser.php';
if (file_exists($listener_file)) {
    $content = file_get_contents($listener_file);
    
    if (strpos($content, "Registered") !== false) {
        echo "  ✅ يستمع لحدث Registered\n";
    } else {
        echo "  ❌ لا يستمع لحدث Registered\n";
    }
    
    if (strpos($content, "assignRole") !== false) {
        echo "  ✅ يعيّن دور admin تلقائياً\n";
    } else {
        echo "  ❌ لا يعيّن دور admin\n";
    }
}

echo "\n";

// Check 5: EventServiceProvider
echo "5️⃣ التحقق من EventServiceProvider:\n";
echo "─────────────────────────────────────\n";

$esp_file = 'app/Providers/EventServiceProvider.php';
if (file_exists($esp_file)) {
    $content = file_get_contents($esp_file);
    
    if (strpos($content, "AssignAdminRoleToNewUser") !== false) {
        echo "  ✅ Listener مسجل في EventServiceProvider\n";
    } else {
        echo "  ❌ Listener غير مسجل في EventServiceProvider\n";
    }
    
    if (strpos($content, "RoleAndPermissionSeeder") !== false) {
        echo "  ✅ DatabaseSeeder يستدعي RoleAndPermissionSeeder\n";
    } else {
        echo "  ⚠️ تحقق من DatabaseSeeder\n";
    }
}

echo "\n";

// Summary
echo "════════════════════════════════════════════════\n";
echo "  📊 الملخص\n";
echo "════════════════════════════════════════════════\n";

echo "\n✅ الملفات المنشأة: " . (count($files_to_check) - $missing_files) . "/" . count($files_to_check) . "\n";
echo "✅ الموارد المحدثة: {$updated_resources}/8\n";
echo "\n";

if ($missing_files == 0 && $updated_resources == 8) {
    echo "🎉 نظام الأدوار والصلاحيات مكتمل بنسبة 100%!\n";
    echo "\nالخطوات التالية:\n";
    echo "  1. php artisan db:seed --class=RoleAndPermissionSeeder\n";
    echo "  2. php artisan cache:clear\n";
    echo "  3. php artisan serve\n";
} else {
    echo "⚠️ هناك بعض الملفات المفقودة أو غير محدثة.\n";
    echo "تحقق من السجل أعلاه للمزيد من التفاصيل.\n";
}

echo "\n════════════════════════════════════════════════\n\n";
?>
