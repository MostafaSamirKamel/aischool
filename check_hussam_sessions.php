<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Instructor;
use App\Models\AttendanceSession;
use App\Models\InstructorPayment;
use App\Models\Group;

echo "=== تشخيص سيشنات حسام ===\n\n";

// البحث عن حسام (Eng Hossam Magdy - ID:1)
$instructors = Instructor::with('user')
    ->withoutGlobalScopes()
    ->whereHas('user', function ($q) {
        $q->where('name', 'like', '%Hossam%')
          ->orWhere('name', 'like', '%حسام%');
    })
    ->get();

if ($instructors->isEmpty()) {
    echo "❌ لم يتم العثور على مدرس باسم حسام!\n\n";
    echo "=== قائمة جميع المدرسين ===\n";
    $all = Instructor::with('user')->withoutGlobalScopes()->get();
    foreach ($all as $i) {
        echo "  ID:{$i->id} - " . ($i->user->name ?? 'بدون مستخدم') . " (tenant:{$i->tenant_id})\n";
    }
    exit;
}

foreach ($instructors as $instructor) {
    echo "✅ المدرس: {$instructor->user->name} (ID: {$instructor->id}, Tenant: {$instructor->tenant_id})\n";
    echo "   سعر الحصة: " . ($instructor->hourly_rate ?? 'غير محدد') . " جنيه\n";
    echo "   الراتب الأساسي: " . ($instructor->base_salary ?? 0) . " جنيه\n\n";

    // المجموعات المرتبطة به
    $groups = Group::withoutGlobalScopes()
        ->where('instructor_id', $instructor->id)
        ->get();

    echo "   عدد المجموعات: {$groups->count()}\n";
    foreach ($groups as $group) {
        echo "     - مجموعة: {$group->name} (ID: {$group->id})\n";
    }
    echo "\n";

    // جميع السيشنات بدون global scope
    $allSessions = AttendanceSession::withoutGlobalScopes()
        ->whereHas('group', function ($q) use ($instructor) {
            $q->withoutGlobalScopes()->where('instructor_id', $instructor->id);
        })
        ->orderBy('attendance_date')
        ->get();

    echo "   📊 إجمالي السيشنات (withoutGlobalScopes): {$allSessions->count()}\n\n";

    // تقسيم السيشنات بالشهر
    $byMonth = $allSessions->groupBy(function ($s) {
        return $s->attendance_date->format('Y-m');
    });

    echo "   📅 توزيع السيشنات بالشهور:\n";
    foreach ($byMonth as $month => $sessions) {
        echo "     - {$month}: {$sessions->count()} سيشن\n";
    }
    echo "\n";

    // السيشنات بالـ global scope (كما يراها النظام)
    $scopedSessions = AttendanceSession::withoutGlobalScopes()
        ->whereHas('group', function ($q) use ($instructor) {
            $q->withoutGlobalScopes()->where('instructor_id', $instructor->id);
        })
        ->where('tenant_id', $instructor->tenant_id)
        ->get();

    echo "   🔍 السيشنات بعد فلتر tenant_id: {$scopedSessions->count()}\n\n";

    // مدفوعات موجودة
    $payments = InstructorPayment::withoutGlobalScopes()
        ->where('instructor_id', $instructor->id)
        ->orderBy('period_start')
        ->get();

    echo "   💰 المدفوعات الموجودة ({$payments->count()} مدفوعة):\n";
    foreach ($payments as $payment) {
        echo "     - {$payment->period_start->format('Y-m-d')} → {$payment->period_end->format('Y-m-d')}: ";
        echo "{$payment->completed_sessions} سيشن, {$payment->total_amount} جنيه ({$payment->status})\n";
    }
    echo "\n";

    // السيشنات التي لا يرتبط مجموعتها بالمدرس
    $orphanSessions = AttendanceSession::withoutGlobalScopes()
        ->where('tenant_id', $instructor->tenant_id)
        ->whereDoesntHave('group', function ($q) use ($instructor) {
            $q->withoutGlobalScopes()->where('instructor_id', $instructor->id);
        })
        ->whereHas('group', function ($q) {
            // فقط السيشنات التي لديها مجموعة
            $q->withoutGlobalScopes();
        })
        ->get();

    if ($orphanSessions->isNotEmpty()) {
        echo "   ⚠️ سيشنات في نفس الـ tenant لكن مجموعتها لا تحمل اسم حسام: {$orphanSessions->count()}\n";
    }

    echo "---\n\n";
}
