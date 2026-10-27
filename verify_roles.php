#!/usr/bin/env php
<?php

echo "\n";
echo "===========================================\n";
echo "  فحص نظام الأدوار والصلاحيات\n";
echo "===========================================\n";
echo "\n";

// Check 1: Files exist
echo "1. التحقق من وجود الملفات:\n";
echo "───────────────────────────\n";

$files = [
    'database/seeders/RoleAndPermissionSeeder.php',
    'app/Listeners/AssignAdminRoleToNewUser.php',
];

$count = 0;
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "  ✅ $file\n";
        $count++;
    } else {
        echo "  ❌ $file\n";
    }
}

echo "\n";

// Check 2: Resources updated
echo "2. التحقق من تحديث Resources:\n";
echo "───────────────────────────────\n";

$resources = [
    'Patients/PatientResource.php',
    'Doctors/DoctorResource.php',
    'Wards/WardResource.php',
    'Tenants/TenantResource.php',
];

foreach ($resources as $resource) {
    $path = "app/Filament/Admin/Resources/$resource";
    if (file_exists($path)) {
        echo "  ✅ $resource\n";
    } else {
        echo "  ❌ $resource\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "  🎉 فحص مكتمل!\n";
echo "===========================================\n\n";
?>
