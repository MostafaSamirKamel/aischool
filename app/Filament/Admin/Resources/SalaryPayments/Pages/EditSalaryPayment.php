<?php

namespace App\Filament\Admin\Resources\SalaryPayments\Pages;

use App\Filament\Admin\Resources\SalaryPayments\SalaryPaymentResource;
use Filament\Resources\Pages\EditRecord;

class EditSalaryPayment extends EditRecord
{
    protected static string $resource = SalaryPaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'paid' && empty($this->record->paid_by)) {
            $data['paid_by'] = auth()->id();
        }

        return $data;
    }
}
