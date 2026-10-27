<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AttendanceSession;
use App\Models\InstructorPayment;
use Illuminate\Support\Facades\DB;

echo "=== إصلاح سجلات الدفع المكررة لجميع المدرسين ===\n\n";

// أيضاً احذف السجلات المحذوفة ناعماً (من التشغيل السابق)
$softDeleted = InstructorPayment::withoutGlobalScopes()
    ->whereNotNull('deleted_at')
    ->count();
if ($softDeleted > 0) {
    echo "حذف {$softDeleted} سجل محذوف ناعماً...\n";
    InstructorPayment::withoutGlobalScopes()->whereNotNull('deleted_at')->forceDelete();
}

// الحصول على جميع سجلات الدفع المكررة (نفس instructor_id + period_start + period_end)
$duplicateGroups = InstructorPayment::withoutGlobalScopes()
    ->select('instructor_id', 'tenant_id', 'period_start', 'period_end', DB::raw('COUNT(*) as cnt'))
    ->groupBy('instructor_id', 'tenant_id', 'period_start', 'period_end')
    ->having('cnt', '>', 1)
    ->get();

echo "عدد مجموعات السجلات المكررة: {$duplicateGroups->count()}\n\n";

$totalDeleted = 0;
$totalFixed = 0;

foreach ($duplicateGroups as $group) {
    // الحصول على جميع السجلات المكررة
    $records = InstructorPayment::withoutGlobalScopes()
        ->where('instructor_id', $group->instructor_id)
        ->where('tenant_id', $group->tenant_id)
        ->where('period_start', $group->period_start)
        ->where('period_end', $group->period_end)
        ->orderBy('id')
        ->get();

    // الاحتفاظ بأعلى سجل status (paid > approved > pending) أو أحدث id
    $keepRecord = $records->sortByDesc(function ($r) {
        $priority = ['paid' => 3, 'approved' => 2, 'pending' => 1, 'cancelled' => 0];
        return ($priority[$r->status] ?? 0) * 1000000 + $r->id;
    })->first();

    $toDelete = $records->where('id', '!=', $keepRecord->id);

    echo "إصلاح: Instructor #{$group->instructor_id} - {$group->period_start} ({$records->count()} سجل → 1)\n";

    // حذف السجلات المكررة نهائياً (forceDelete لأن النموذج يستخدم SoftDeletes)
    InstructorPayment::withoutGlobalScopes()
        ->whereIn('id', $toDelete->pluck('id'))
        ->forceDelete();

    $totalDeleted += $toDelete->count();

    // حساب السيشنات الفعلية لهذه الفترة
    $periodStart = \Carbon\Carbon::parse($group->period_start);
    $periodEnd = \Carbon\Carbon::parse($group->period_end);

    $sessions = AttendanceSession::withoutGlobalScopes()
        ->whereHas('group', function ($q) use ($group) {
            $q->withoutGlobalScopes()->where('instructor_id', $group->instructor_id);
        })
        ->where('tenant_id', $group->tenant_id)
        ->whereBetween('attendance_date', [$periodStart, $periodEnd])
        ->with('group')
        ->get();

    $totalSessions = $sessions->count();
    $ratePerSession = $keepRecord->rate_per_session > 0 ? $keepRecord->rate_per_session : 200;
    $sessionsAmount = $totalSessions * $ratePerSession;
    $baseAmount = $keepRecord->base_amount ?? 0;

    $sessionsData = $sessions->map(function ($s) use ($ratePerSession) {
        return [
            'id' => $s->id,
            'date' => $s->attendance_date->format('Y-m-d'),
            'group' => $s->group->name ?? 'غير محدد',
            'rate' => $ratePerSession,
        ];
    })->toArray();

    $keepRecord->update([
        'total_sessions' => $totalSessions,
        'completed_sessions' => $totalSessions,
        'cancelled_sessions' => 0,
        'rate_per_session' => $ratePerSession,
        'sessions_amount' => $sessionsAmount,
        'base_amount' => $baseAmount,
        'total_amount' => $baseAmount + $sessionsAmount + ($keepRecord->bonus_amount ?? 0) - ($keepRecord->deduction_amount ?? 0),
        'sessions_data' => $sessionsData,
    ]);

    echo "  ✓ تم الإصلاح: {$totalSessions} سيشن, " . number_format($keepRecord->total_amount, 2) . " جنيه\n";
    $totalFixed++;
}

echo "\n=== ملخص ===\n";
echo "سجلات محذوفة: {$totalDeleted}\n";
echo "سجلات مُصلحة: {$totalFixed}\n\n";

// إضافة unique index لمنع التكرار مستقبلاً
try {
    DB::statement('ALTER TABLE instructor_payments ADD UNIQUE INDEX unique_instructor_period (instructor_id, tenant_id, period_start, period_end)');
    echo "✅ تم إضافة UNIQUE INDEX لمنع التكرار مستقبلاً\n";
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'already exists')) {
        echo "ℹ️ UNIQUE INDEX موجود بالفعل\n";
    } else {
        echo "⚠️ لم يتم إضافة UNIQUE INDEX: " . $e->getMessage() . "\n";
        echo "   (يُنصح بإضافته يدوياً من phpMyAdmin)\n";
    }
}

echo "\n=== التحقق النهائي لـ Eng Hossam Magdy ===\n";
$finalPayments = InstructorPayment::withoutGlobalScopes()
    ->whereNull('deleted_at')
    ->where('instructor_id', 1)
    ->orderBy('period_start')
    ->get();

echo "عدد سجلات الدفع بعد الإصلاح: {$finalPayments->count()}\n";
foreach ($finalPayments as $p) {
    echo "  - {$p->period_start->format('Y-m')}: {$p->completed_sessions} سيشن = " . number_format($p->total_amount, 2) . " جنيه ({$p->status})\n";
}

$totalSessions = $finalPayments->sum('completed_sessions');
$totalAmount = $finalPayments->sum('total_amount');
echo "\nإجمالي السيشنات: {$totalSessions}\n";
echo "إجمالي المبلغ المستحق: " . number_format($totalAmount, 2) . " جنيه\n";
