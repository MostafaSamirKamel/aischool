<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n📊 Student System Data Check:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Check actual database
echo "📈 Database Direct Check:\n";
$dbStudents = DB::table('students')->count();
echo "  ├─ Students in DB (raw): {$dbStudents}\n";
echo "  ├─ Student codes: ";
if ($dbStudents > 0) {
    $codes = DB::table('students')->pluck('student_code')->toArray();
    echo implode(', ', array_slice($codes, 0, 10));
    if (count($codes) > 10) {
        echo " ... (+" . (count($codes) - 10) . " more)";
    }
    echo "\n";
} else {
    echo "None\n";
}

// Eloquent counts (without global scopes)
echo "\n📈 Eloquent Counts (without branch scope):\n";
echo "  ├─ Students: " . \App\Models\Student::withoutGlobalScopes()->count() . "\n";
echo "  ├─ Enrollments: " . \App\Models\Enrollment::withoutGlobalScopes()->count() . "\n";
echo "  ├─ Payments: " . \App\Models\Payment::withoutGlobalScopes()->count() . "\n";
echo "  └─ Student-Parent Links: " . \App\Models\StudentParent::count() . "\n\n";

// Users by role
echo "👥 Users by Role:\n";
echo "  ├─ Total Users: " . \App\Models\User::count() . "\n";
echo "  ├─ Students (role): " . \App\Models\User::role('student')->count() . "\n";
echo "  ├─ Parents (role): " . \App\Models\User::role('parent')->count() . "\n";
echo "  └─ Others: " . (\App\Models\User::count() - \App\Models\User::role(['student', 'parent'])->count()) . "\n\n";

// Per branch
echo "📍 Per Branch:\n";
foreach (\App\Models\Tenant::all() as $branch) {
    $students = \App\Models\Student::withoutGlobalScopes()->where('tenant_id', $branch->id)->count();
    $enrollments = \App\Models\Enrollment::withoutGlobalScopes()->where('tenant_id', $branch->id)->count();
    $payments = \App\Models\Payment::withoutGlobalScopes()->where('tenant_id', $branch->id)->count();
    
    echo "  ├─ {$branch->name}:\n";
    echo "  │  ├─ Students: {$students}\n";
    echo "  │  ├─ Enrollments: {$enrollments}\n";
    echo "  │  └─ Payments: {$payments}\n";
}

echo "\n✅ Data check complete!\n\n";
