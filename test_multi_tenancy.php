<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\Course;
use App\Models\Group;
use App\Models\Enrollment;
use App\Models\Track;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "🔍 Testing Multi-Tenancy Scopes\n";
echo str_repeat('═', 60) . "\n\n";

// 1. Test without authentication
echo "1️⃣ Without Authentication (should return 0):\n";
Auth::logout();
echo "  Students: " . Student::count() . "\n";
echo "  Courses: " . Course::count() . "\n";
echo "  Groups: " . Group::count() . "\n\n";

// 2. Test as Super Admin (tenant_id = null)
echo "2️⃣ As Super Admin (tenant_id = null) - should see ALL:\n";
$superAdmin = User::where('is_super_admin', true)->orWhereNull('tenant_id')->first();
if ($superAdmin) {
    Auth::login($superAdmin);
    echo "  👤 Logged in as: {$superAdmin->name}\n";
    echo "  📊 tenant_id: " . ($superAdmin->tenant_id ?? 'NULL') . "\n";
    echo "  📚 Students: " . Student::count() . "\n";
    echo "  📚 Courses: " . Course::count() . "\n";
    echo "  📚 Groups: " . Group::count() . "\n";
    echo "  📚 Enrollments: " . Enrollment::count() . "\n";
    Auth::logout();
} else {
    echo "  ❌ No Super Admin found\n";
}
echo "\n";

// 3. Test as Branch User (with tenant_id)
echo "3️⃣ As Branch User (with tenant_id) - should see ONLY their branch:\n";
$branchUser = User::whereNotNull('tenant_id')->where('is_super_admin', false)->first();
if ($branchUser) {
    Auth::login($branchUser);
    echo "  👤 Logged in as: {$branchUser->name}\n";
    echo "  📊 tenant_id: {$branchUser->tenant_id}\n";
    echo "  🏢 Branch: {$branchUser->tenant->name}\n";
    echo "  📚 Students: " . Student::count() . "\n";
    echo "  📚 Courses: " . Course::count() . "\n";
    echo "  📚 Groups: " . Group::count() . "\n";
    echo "  📚 Enrollments: " . Enrollment::count() . "\n";
    
    // Check if filtering works
    $students = Student::get();
    $allSameTenant = $students->every(fn($s) => $s->tenant_id == $branchUser->tenant_id);
    echo "  ✅ All students belong to same tenant: " . ($allSameTenant ? 'YES' : 'NO') . "\n";
    Auth::logout();
} else {
    echo "  ❌ No Branch User found\n";
}
echo "\n";

// 4. Test withoutGlobalScopes
echo "4️⃣ Without Global Scopes (should see ALL):\n";
echo "  Students: " . Student::withoutGlobalScopes()->count() . "\n";
echo "  Courses: " . Course::withoutGlobalScopes()->count() . "\n";
echo "  Groups: " . Group::withoutGlobalScopes()->count() . "\n\n";

// 5. Test per branch
echo "5️⃣ Data per Branch:\n";
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    echo "  🏢 {$tenant->name}:\n";
    echo "     Students: " . Student::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count() . "\n";
    echo "     Courses: " . Course::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count() . "\n";
    echo "     Groups: " . Group::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count() . "\n";
}

echo "\n✅ Multi-Tenancy Test Complete!\n";
