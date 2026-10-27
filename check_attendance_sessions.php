<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;

echo "=== فحص جلسات الحضور ===\n\n";

// احصل على الجلسات بدون global scope
$sessionsRaw = DB::table('attendance_sessions')->get();
echo "جلسات الحضور (بدون scope): {$sessionsRaw->count()}\n";

if ($sessionsRaw->count() > 0) {
    echo "\nتفاصيل الجلسات:\n";
    foreach ($sessionsRaw as $session) {
        echo "  - Session ID: {$session->id} | Group: {$session->group_id} | Course: {$session->course_id} | Tenant: {$session->tenant_id}\n";
    }
}

// احصل على الجلسات مع global scope
$sessionsModel = AttendanceSession::get();
echo "\nجلسات الحضور (مع scope): {$sessionsModel->count()}\n";

// تحقق من المجموعة 6
echo "\n=== فحص المجموعة 6 ===\n";
$group6Sessions = DB::table('attendance_sessions')
    ->where('group_id', 6)
    ->get();
echo "جلسات للمجموعة 6: {$group6Sessions->count()}\n";

foreach ($group6Sessions as $s) {
    echo "  - ID: {$s->id} | Date: {$s->attendance_date} | Tenant: {$s->tenant_id}\n";
}

echo "\n";
