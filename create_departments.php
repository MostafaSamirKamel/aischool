<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Department;

$tenants = Tenant::all();

if ($tenants->isEmpty()) {
    echo "No tenants found!\n";
    exit(1);
}

$departmentNames = [
    'قسم الرياضيات',
    'قسم العلوم',
    'قسم اللغات',
    'قسم العلوم الاجتماعية',
];

foreach ($tenants as $tenant) {
    echo "Creating departments for tenant: {$tenant->name}\n";
    
    foreach ($departmentNames as $departmentName) {
        Department::create([
            'name' => $departmentName,
            'tenant_id' => $tenant->id,
        ]);
    }
}

echo "\nDepartments created successfully!\n";
