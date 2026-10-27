<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Vault;
use App\Models\SupervisionGroupAssignment;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== TENANTS / BRANCHES ===\n";
foreach (Tenant::all() as $t) {
    echo "ID: {$t->id} | Name: {$t->name}\n";
}

echo "\n=== COURSES ===\n";
foreach (Course::take(5)->get() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | Tenant: {$c->tenant_id}\n";
}

echo "\n=== SAMPLE GROUPS ===\n";
foreach (Group::take(6)->get() as $g) {
    echo "ID: {$g->id} | Name: {$g->name} | Course: {$g->course_id} | Instructor: {$g->instructor_id} | Tenant: {$g->tenant_id}\n";
}

echo "\n=== VAULTS ===\n";
foreach (Vault::all() as $v) {
    echo "ID: {$v->id} | Name: {$v->name} | Type: {$v->type} | Balance: {$v->current_balance} | Tenant: {$v->tenant_id}\n";
}

echo "\n=== SUPERVISION GROUP ASSIGNMENTS ===\n";
foreach (SupervisionGroupAssignment::take(10)->get() as $sga) {
    echo "User: {$sga->user_id} | Group: {$sga->group_id} | Revoked: " . ($sga->revoked_at ?? 'Active') . "\n";
}
