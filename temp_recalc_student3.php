<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function recalculateStudentInstallments(int $studentId): array
{
    $enrollmentsForAllocation = App\Models\Enrollment::withoutGlobalScopes()
        ->where('student_id', $studentId)
        ->with([
            'installments' => function ($query) {
                $query->withoutGlobalScopes();
            },
        ])
        ->get();

    if ($enrollmentsForAllocation->isEmpty()) {
        return [
            'student_id' => $studentId,
            'processed' => false,
            'reason' => 'no_enrollments',
        ];
    }

    $enrollmentIds = $enrollmentsForAllocation->pluck('id')->all();

    $overdueBefore = App\Models\Installment::withoutGlobalScopes()
        ->whereIn('enrollment_id', $enrollmentIds)
        ->where('status', '!=', 'cancelled')
        ->whereColumn('paid_amount', '<', 'amount')
        ->where('due_date', '<', now()->startOfDay())
        ->count();

    $installmentEnrollments = $enrollmentsForAllocation
        ->filter(fn(App\Models\Enrollment $enrollment): bool => $enrollment->payment_plan === 'installments')
        ->values();

    if ($installmentEnrollments->isEmpty()) {
        return [
            'student_id' => $studentId,
            'processed' => false,
            'reason' => 'no_installment_enrollments',
            'overdue_before' => $overdueBefore,
            'overdue_after' => $overdueBefore,
        ];
    }

    $installments = collect();

    foreach ($installmentEnrollments as $enrollment) {
        foreach ($enrollment->installments as $installment) {
            $installments->push($installment);
        }
    }

    $installments = $installments
        ->sortBy(function (App\Models\Installment $installment): string {
            $dueDate = $installment->due_date
                ? \Carbon\Carbon::parse($installment->due_date)->format('Y-m-d')
                : '9999-12-31';

            return sprintf('%s-%05d-%010d', $dueDate, (int) $installment->installment_number, (int) $installment->id);
        })
        ->values();

    foreach ($installments as $installment) {
        /** @var App\Models\Installment $installment */
        $installment->forceFill(['paid_amount' => 0.0]);
        $installment->paid_date = null;

        $isOverdue = $installment->due_date
            ? now()->startOfDay()->gt(\Carbon\Carbon::parse($installment->due_date)->startOfDay())
            : false;

        $installment->status = $isOverdue ? 'overdue' : 'pending';
        $installment->save();
    }

    $completedPayments = App\Models\Payment::withoutGlobalScopes()
        ->whereIn('enrollment_id', $enrollmentIds)
        ->where('status', 'completed')
        ->orderBy('payment_date', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    foreach ($completedPayments as $completedPayment) {
        $remainingAmount = (float) $completedPayment->amount;

        foreach ($installments as $installment) {
            if ($remainingAmount <= 0) {
                break;
            }

            $installmentRemaining = (float) $installment->amount - (float) $installment->paid_amount;
            if ($installmentRemaining <= 0) {
                continue;
            }

            $amountToApply = min($remainingAmount, $installmentRemaining);

            $installment->forceFill([
                'paid_amount' => round((float) $installment->paid_amount + $amountToApply, 2),
            ]);

            if ($installment->paid_amount > 0 && !$installment->paid_date) {
                $installment->paid_date = $completedPayment->payment_date;
            }

            if ((float) $installment->paid_amount >= (float) $installment->amount) {
                $installment->status = 'paid';
            } elseif ((float) $installment->paid_amount > 0) {
                $installment->status = 'partial';
            } elseif ($installment->due_date && now()->startOfDay()->gt(\Carbon\Carbon::parse($installment->due_date)->startOfDay())) {
                $installment->status = 'overdue';
            } else {
                $installment->status = 'pending';
            }

            $installment->save();
            $remainingAmount -= $amountToApply;
        }
    }

    foreach ($installmentEnrollments as $enrollment) {
        $upfrontAmount = App\Models\Enrollment::withoutGlobalScopes()->find($enrollment->id)?->getCalculatedUpfrontAmount() ?? 0.0;
        $totalPaidFromInstallments = (float) App\Models\Installment::withoutGlobalScopes()
            ->where('enrollment_id', $enrollment->id)
            ->sum('paid_amount');
        $effectivePaid = min($enrollment->getNetAmount(), $upfrontAmount + $totalPaidFromInstallments);

        App\Models\Enrollment::withoutGlobalScopes()
            ->whereKey($enrollment->id)
            ->update(['paid_amount' => $effectivePaid]);
    }

    $overdueAfter = App\Models\Installment::withoutGlobalScopes()
        ->whereIn('enrollment_id', $enrollmentIds)
        ->where('status', '!=', 'cancelled')
        ->whereColumn('paid_amount', '<', 'amount')
        ->where('due_date', '<', now()->startOfDay())
        ->count();

    return [
        'student_id' => $studentId,
        'processed' => true,
        'enrollments_count' => count($enrollmentIds),
        'installment_enrollments_count' => $installmentEnrollments->count(),
        'installments_count' => $installments->count(),
        'completed_payments_count' => $completedPayments->count(),
        'overdue_before' => $overdueBefore,
        'overdue_after' => $overdueAfter,
    ];
}

echo 'Starting installments recalculation for all students...' . PHP_EOL;

$studentIds = App\Models\Enrollment::withoutGlobalScopes()
    ->select('student_id')
    ->distinct()
    ->orderBy('student_id')
    ->pluck('student_id')
    ->all();

$processedStudents = 0;
$changedStudents = 0;
$skippedStudents = 0;
$totalOverdueBefore = 0;
$totalOverdueAfter = 0;

foreach ($studentIds as $studentId) {
    $result = recalculateStudentInstallments((int) $studentId);

    if (!($result['processed'] ?? false)) {
        $skippedStudents++;
        echo "student_id={$studentId} skipped reason={$result['reason']}" . PHP_EOL;
        continue;
    }

    $processedStudents++;
    $totalOverdueBefore += (int) $result['overdue_before'];
    $totalOverdueAfter += (int) $result['overdue_after'];

    if ((int) $result['overdue_before'] !== (int) $result['overdue_after']) {
        $changedStudents++;
    }
    echo sprintf(
        'student_id=%d enrollments=%d installment_enrollments=%d installments=%d payments=%d overdue_before=%d overdue_after=%d',
        $studentId,
        $result['enrollments_count'],
        $result['installment_enrollments_count'],
        $result['installments_count'],
        $result['completed_payments_count'],
        $result['overdue_before'],
        $result['overdue_after']
    ) . PHP_EOL;
}

echo str_repeat('-', 80) . PHP_EOL;
echo 'Done.' . PHP_EOL;
echo "students_found=" . count($studentIds) . PHP_EOL;
echo "students_processed={$processedStudents}" . PHP_EOL;
echo "students_changed={$changedStudents}" . PHP_EOL;
echo "students_skipped={$skippedStudents}" . PHP_EOL;
echo "total_overdue_before={$totalOverdueBefore}" . PHP_EOL;
echo "total_overdue_after={$totalOverdueAfter}" . PHP_EOL;
