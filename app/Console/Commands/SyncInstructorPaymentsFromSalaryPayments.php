<?php

namespace App\Console\Commands;

use App\Models\InstructorPayment;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncInstructorPaymentsFromSalaryPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary-payments:sync-instructor-history
                            {--tenant_id= : Sync only one tenant}
                            {--instructor_id= : Sync only one instructor}
                            {--from_date= : Start date filter (Y-m-d), applied on payment_date or created_at}
                            {--to_date= : End date filter (Y-m-d), applied on payment_date or created_at}
                            {--apply : Apply updates (default is dry-run)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync legacy paid salary payments to instructor monthly payment status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant_id');
        $instructorId = $this->option('instructor_id');
        $fromDateInput = $this->option('from_date');
        $toDateInput = $this->option('to_date');
        $apply = (bool) $this->option('apply');
        $dryRun = !$apply;

        $fromDate = $this->parseDateOption($fromDateInput, 'from_date');
        $toDate = $this->parseDateOption($toDateInput, 'to_date', true);

        if (($fromDateInput && !$fromDate) || ($toDateInput && !$toDate)) {
            return Command::INVALID;
        }

        if ($fromDate && $toDate && $toDate->lt($fromDate)) {
            $this->error('The to_date must be greater than or equal to from_date.');
            return Command::INVALID;
        }

        $query = SalaryPayment::withoutGlobalScopes()
            ->where('employee_type', 'instructor')
            ->where('status', 'paid')
            ->whereNotNull('tenant_id')
            ->whereNotNull('instructor_id')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', (int) $tenantId))
            ->when($instructorId, fn ($q) => $q->where('instructor_id', (int) $instructorId));

        if ($fromDate || $toDate) {
            $query->where(function ($q) use ($fromDate, $toDate) {
                if ($fromDate) {
                    $q->where(function ($inner) use ($fromDate) {
                        $inner->whereDate('payment_date', '>=', $fromDate->toDateString())
                            ->orWhere(function ($fallback) use ($fromDate) {
                                $fallback->whereNull('payment_date')
                                    ->whereDate('created_at', '>=', $fromDate->toDateString());
                            });
                    });
                }

                if ($toDate) {
                    $q->where(function ($inner) use ($toDate) {
                        $inner->whereDate('payment_date', '<=', $toDate->toDateString())
                            ->orWhere(function ($fallback) use ($toDate) {
                                $fallback->whereNull('payment_date')
                                    ->whereDate('created_at', '<=', $toDate->toDateString());
                            });
                    });
                }
            });
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No paid instructor salary payments found for the selected filters.');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} paid salary payment(s) to process.");
        if ($dryRun) {
            $this->warn('Running in dry-run mode. Use --apply to persist updates.');
        }

        $matched = 0;
        $updated = 0;
        $missingInstructorPayments = 0;

        $query->orderBy('id')->chunkById(200, function ($salaryPayments) use (&$matched, &$updated, &$missingInstructorPayments, $dryRun) {
            foreach ($salaryPayments as $salaryPayment) {
                $matched++;

                $effectivePaymentDate = $salaryPayment->payment_date
                    ? Carbon::parse($salaryPayment->payment_date)->toDateString()
                    : Carbon::parse($salaryPayment->updated_at ?? $salaryPayment->created_at ?? now())->toDateString();

                $instructorPayment = InstructorPayment::withoutGlobalScopes()
                    ->where('tenant_id', $salaryPayment->tenant_id)
                    ->where('instructor_id', $salaryPayment->instructor_id)
                    ->whereDate('period_start', Carbon::parse($salaryPayment->period_start)->toDateString())
                    ->whereDate('period_end', Carbon::parse($salaryPayment->period_end)->toDateString())
                    ->first();

                if (!$instructorPayment) {
                    $missingInstructorPayments++;
                    $this->line("[MISSING] SalaryPayment #{$salaryPayment->id} -> no InstructorPayment found for the same period.");
                    continue;
                }

                $changes = [];

                if ($instructorPayment->status !== 'paid') {
                    $changes['status'] = 'paid';
                }

                if (!$instructorPayment->payment_date || Carbon::parse($instructorPayment->payment_date)->toDateString() !== $effectivePaymentDate) {
                    $changes['payment_date'] = $effectivePaymentDate;
                }

                if (($salaryPayment->payment_method ?? null) !== ($instructorPayment->payment_method ?? null)) {
                    $changes['payment_method'] = $salaryPayment->payment_method;
                }

                $targetPaidBy = $salaryPayment->paid_by ?? $salaryPayment->created_by;
                if ($targetPaidBy && (int) ($instructorPayment->paid_by ?? 0) !== (int) $targetPaidBy) {
                    $changes['paid_by'] = $targetPaidBy;
                }

                if (empty($changes)) {
                    continue;
                }

                if (!$dryRun) {
                    $instructorPayment->update($changes);
                }

                $updated++;
                $this->line("[SYNC] SalaryPayment #{$salaryPayment->id} -> InstructorPayment #{$instructorPayment->id} ({$instructorPayment->period_label})");
            }
        });

        $this->newLine();
        $this->info('Sync finished.');
        $this->info('Processed salary payments: ' . $matched);
        $this->info('Instructor payments updated: ' . $updated . ($dryRun ? ' (dry-run)' : ''));

        if ($missingInstructorPayments > 0) {
            $this->warn('Missing instructor payment snapshots: ' . $missingInstructorPayments);
        }

        return Command::SUCCESS;
    }

    private function parseDateOption(?string $value, string $optionName, bool $endOfDay = false): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
            return $endOfDay ? $date->endOfDay() : $date->startOfDay();
        } catch (\Throwable $e) {
            $this->error("Invalid {$optionName} value: {$value}. Expected date format like Y-m-d.");
            return null;
        }
    }
}
