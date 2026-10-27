<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use Illuminate\Console\Command;

class RecalculateEnrollmentFinancials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollments:recalculate-financials
                            {--student_id= : Recalculate for a specific student}
                            {--enrollment_id= : Recalculate for a specific enrollment}
                            {--dry-run : Show what would change without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate installments status and paid totals for enrollments';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $studentId = $this->option('student_id');
        $enrollmentId = $this->option('enrollment_id');
        $dryRun = (bool) $this->option('dry-run');

        $query = Enrollment::query()
            ->with(['installments', 'payments'])
            ->where('payment_plan', 'installments')
            ->when($studentId, fn ($q) => $q->where('student_id', (int) $studentId))
            ->when($enrollmentId, fn ($q) => $q->where('id', (int) $enrollmentId));

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('No installment enrollments found for the selected filters.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . $total . ' enrollment(s).');
        if ($dryRun) {
            $this->warn('Running in dry-run mode. No data will be updated.');
        }

        $updated = 0;

        $query->chunkById(200, function ($enrollments) use (&$updated, $dryRun) {
            foreach ($enrollments as $enrollment) {
                if (!$enrollment instanceof Enrollment) {
                    continue;
                }

                if ($enrollment->installments->isEmpty()) {
                    continue;
                }

                $originalPaidAmount = (float) $enrollment->paid_amount;

                $workingInstallments = $enrollment->installments
                    ->sortBy([['due_date', 'asc'], ['installment_number', 'asc']])
                    ->values();

                $installmentsData = $workingInstallments->map(function ($installment) {
                    $isOverdue = $installment->due_date
                        ? now()->startOfDay()->gt(\Carbon\Carbon::parse($installment->due_date)->startOfDay())
                        : false;

                    return [
                        'id' => $installment->id,
                        'amount' => (float) $installment->amount,
                        'paid_amount' => 0.0,
                        'paid_date' => null,
                        'status' => $isOverdue ? 'overdue' : 'pending',
                    ];
                })->toArray();

                $completedPayments = $enrollment->payments
                    ->where('status', 'completed')
                    ->sortBy([['payment_date', 'asc'], ['id', 'asc']])
                    ->values();

                foreach ($completedPayments as $payment) {
                    $remainingAmount = (float) $payment->amount;

                    foreach ($installmentsData as &$installmentData) {
                        if ($remainingAmount <= 0) {
                            break;
                        }

                        $remainingForInstallment = $installmentData['amount'] - $installmentData['paid_amount'];
                        if ($remainingForInstallment <= 0) {
                            continue;
                        }

                        $amountToApply = min($remainingAmount, $remainingForInstallment);
                        $installmentData['paid_amount'] += $amountToApply;

                        if ($installmentData['paid_amount'] > 0 && empty($installmentData['paid_date'])) {
                            $installmentData['paid_date'] = $payment->payment_date;
                        }

                        if ($installmentData['paid_amount'] >= $installmentData['amount']) {
                            $installmentData['status'] = 'paid';
                        } elseif ($installmentData['paid_amount'] > 0) {
                            $installmentData['status'] = 'partial';
                        }

                        $remainingAmount -= $amountToApply;
                    }
                    unset($installmentData);
                }

                $installmentsPaid = array_sum(array_map(fn ($item) => (float) $item['paid_amount'], $installmentsData));
                $upfront = $enrollment->getCalculatedUpfrontAmount();
                $effectivePaid = min($enrollment->getNetAmount(), $upfront + $installmentsPaid);

                $hasInstallmentChanges = $workingInstallments->contains(function ($installment) use ($installmentsData) {
                    $newData = collect($installmentsData)->firstWhere('id', $installment->id);
                    if (!$newData) {
                        return false;
                    }

                    $oldPaidDate = $installment->paid_date?->format('Y-m-d');
                    $newPaidDate = !empty($newData['paid_date'])
                        ? \Carbon\Carbon::parse($newData['paid_date'])->format('Y-m-d')
                        : null;

                    return (float) $installment->paid_amount !== (float) $newData['paid_amount']
                        || (string) $installment->status !== (string) $newData['status']
                        || (string) $oldPaidDate !== (string) $newPaidDate;
                });

                $hasPaidAmountChange = abs($originalPaidAmount - $effectivePaid) > 0.0001;

                if (!$hasInstallmentChanges && !$hasPaidAmountChange) {
                    continue;
                }

                if (!$dryRun) {
                    foreach ($workingInstallments as $installment) {
                        $newData = collect($installmentsData)->firstWhere('id', $installment->id);
                        if (!$newData) {
                            continue;
                        }

                        $installment->update([
                            'paid_amount' => $newData['paid_amount'],
                            'paid_date' => $newData['paid_date'],
                            'status' => $newData['status'],
                        ]);
                    }

                    Enrollment::whereKey($enrollment->id)->update([
                        'paid_amount' => $effectivePaid,
                    ]);
                }

                $updated++;
                $this->line(sprintf(
                    'Enrollment #%d: paid_amount %.2f -> %.2f',
                    $enrollment->id,
                    $originalPaidAmount,
                    $effectivePaid
                ));
            }
        });

        $this->newLine();
        $this->info('Finished financial recalculation.');
        $this->info('Updated enrollments: ' . $updated);

        return Command::SUCCESS;
    }
}
