<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Group;
use App\Models\Vault;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

echo "================================================================" . PHP_EOL;
echo "  TEST ONLINE BRANCH TENANT ISOLATION & SECURITY BOUNDARIES     " . PHP_EOL;
echo "================================================================" . PHP_EOL;

$onlineUser = User::where('username', '01501101213')->firstOrFail();
Auth::guard('web')->login($onlineUser);

echo "Logged in as: {$onlineUser->name} (Username: {$onlineUser->username})" . PHP_EOL;
echo "Active Tenant ID: {$onlineUser->tenant_id} (Branch: " . ($onlineUser->tenant->name ?? 'N/A') . ")" . PHP_EOL;

// 1. Check Student isolation under TenantScope
$visibleStudents = Student::all();
echo PHP_EOL . "1. Student Isolation Check:" . PHP_EOL;
echo " - Total Students visible to Online Manager: " . $visibleStudents->count() . PHP_EOL;
$exposedMatamirStudents = $visibleStudents->where('tenant_id', 1)->count();
echo " - Abu El Matamir Students exposed: {$exposedMatamirStudents} (Expected: 0)" . PHP_EOL;

// 2. Direct Query for Abu El Matamir Student 01007287273
$specificMatamirStudent = Student::where('phone', '01007287273')->first();
echo " - Query for Matamir Student (01007287273): " . ($specificMatamirStudent ? 'LEAKED (FAIL)' : 'ISOLATED (PASS)') . PHP_EOL;

// 3. Group isolation
$visibleGroups = Group::all();
echo PHP_EOL . "2. Group Isolation Check:" . PHP_EOL;
echo " - Total Groups visible to Online Manager: " . $visibleGroups->count() . PHP_EOL;
$exposedMatamirGroups = $visibleGroups->where('tenant_id', 1)->count();
echo " - Abu El Matamir Groups exposed: {$exposedMatamirGroups} (Expected: 0)" . PHP_EOL;

$specificMatamirGroup = Group::where('name', 'Group A — Python Junior')->first();
echo " - Query for 'Group A — Python Junior': " . ($specificMatamirGroup ? 'LEAKED (FAIL)' : 'ISOLATED (PASS)') . PHP_EOL;

// 4. Vault isolation
$visibleVaults = Vault::all();
echo PHP_EOL . "3. Vault & Financial Isolation Check:" . PHP_EOL;
echo " - Total Vaults visible to Online Manager: " . $visibleVaults->count() . PHP_EOL;
$exposedMatamirVaults = $visibleVaults->where('tenant_id', 1)->count();
echo " - Abu El Matamir Vaults exposed: {$exposedMatamirVaults} (Expected: 0)" . PHP_EOL;

// 5. Final Isolation Status
if ($exposedMatamirStudents === 0 && $exposedMatamirGroups === 0 && $exposedMatamirVaults === 0 && !$specificMatamirStudent && !$specificMatamirGroup) {
    echo PHP_EOL . ">>> MULTI-TENANT ISOLATION: 100% ENFORCED & VERIFIED! <<<" . PHP_EOL;
} else {
    echo PHP_EOL . ">>> SECURITY BREACH: TENANT ISOLATION LEAK! <<<" . PHP_EOL;
}
