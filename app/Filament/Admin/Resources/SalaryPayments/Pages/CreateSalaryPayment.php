<?php

namespace App\Filament\Admin\Resources\SalaryPayments\Pages;

use App\Filament\Admin\Resources\SalaryPayments\SalaryPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalaryPayment extends CreateRecord
{
    protected static string $resource = SalaryPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;

        if (($data['status'] ?? 'pending') === 'paid') {
            $data['paid_by'] = auth()->id();
        }

        return $data;
    }
}
