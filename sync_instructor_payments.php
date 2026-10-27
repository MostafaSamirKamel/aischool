<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AttendanceSession;
use App\Models\InstructorPayment;
use Illuminate\Support\Carbon;

echo "=== مزامنة أجور المدرسين بناءً على جلسات الحضور ===\n\n";

// Get all attendance sessions
$sessions = AttendanceSession::with(['group.instructor'])
    ->whereHas('group.instructor')
    ->get();

echo "عدد جلسات الحضور: " . $sessions->count() . "\n\n";

// Group sessions by instructor and month
$grouped = $sessions->groupBy(function($session) {
    $instructor = $session->group->instructor;
    $month = $session->attendance_date->format('Y-m');
    return $instructor->id . '-' . $month;
});

echo "عدد المجموعات (مدرس-شهر): " . $grouped->count() . "\n\n";

foreach ($grouped as $key => $groupSessions) {
    $firstSession = $groupSessions->first();
    $instructor = $firstSession->group->instructor;
    $periodStart = $firstSession->attendance_date->copy()->startOfMonth();
    $periodEnd = $firstSession->attendance_date->copy()->endOfMonth();
    
    echo "معالجة: {$instructor->user->name} - {$periodStart->format('Y-m')}\n";
    echo "  عدد الجلسات: {$groupSessions->count()}\n";
    
    // Get or create payment
    $payment = InstructorPayment::firstOrCreate(
        [
            'instructor_id' => $instructor->id,
            'tenant_id' => $firstSession->tenant_id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ],
        [
            'period_label' => $periodStart->format('F Y'),
            'total_sessions' => 0,
            'completed_sessions' => 0,
            'cancelled_sessions' => 0,
            'rate_per_session' => $instructor->hourly_rate ?? 100,
            'base_amount' => $instructor->base_salary ?? 0,
            'sessions_amount' => 0,
            'bonus_amount' => 0,
            'deduction_amount' => 0,
            'total_amount' => $instructor->base_salary ?? 0,
            'status' => 'pending',
            'sessions_data' => [],
        ]
    );
    
    // Recalculate
    $totalSessions = $groupSessions->count();
    $ratePerSession = $instructor->hourly_rate ?? 100;
    $sessionsAmount = $totalSessions * $ratePerSession;
    
    $sessionsData = $groupSessions->map(function ($s) use ($ratePerSession) {
        return [
            'id' => $s->id,
            'date' => $s->attendance_date->format('Y-m-d'),
            'group' => $s->group->name ?? 'غير محدد',
            'rate' => $ratePerSession,
        ];
    })->toArray();
    
    $payment->update([
        'total_sessions' => $totalSessions,
        'completed_sessions' => $totalSessions,
        'cancelled_sessions' => 0,
        'rate_per_session' => $ratePerSession,
        'sessions_amount' => $sessionsAmount,
        'total_amount' => $payment->base_amount + $sessionsAmount + $payment->bonus_amount - $payment->deduction_amount,
        'sessions_data' => $sessionsData,
    ]);
    
    echo "  ✓ تم التحديث - المبلغ الإجمالي: " . number_format($payment->total_amount, 2) . " جنيه\n\n";
}

echo "=== تم الانتهاء من المزامنة ===\n";
