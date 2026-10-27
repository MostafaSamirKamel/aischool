<?php

namespace App\Filament\Admin\Resources\Enrollments\Widgets;

use Filament\Widgets\Widget;

class EnrollmentInstallmentsWidget extends Widget
{
    protected string $view = 'filament.admin.resources.enrollments.widgets.enrollment-installments';

    protected int | string | array $columnSpan = 'full';

    public function getRecord()
    {
        return $this->record ?? null;
    }

    public function getInstallments()
    {
        $record = $this->getRecord();
        if (!$record) {
            return collect();
        }

        $installments = $record->installments()
            ->orderBy('installment_number', 'asc')
            ->get();

        foreach ($installments as $installment) {
            $installment->distributed_paid = (float) $installment->paid_amount;
            $installment->distributed_status = $installment->status;
        }

        return $installments;
    }

    public function getStats()
    {
        $installments = $this->getInstallments();

        return [
            'total_count' => $installments->count(),
            'total_amount' => $installments->sum('amount'),
            'total_paid' => $installments->sum(fn($i) => $i->distributed_paid ?? 0),
            'total_remaining' => $installments->sum(fn($i) => ($i->amount ?? 0) - ($i->distributed_paid ?? 0)),
        ];
    }
}
