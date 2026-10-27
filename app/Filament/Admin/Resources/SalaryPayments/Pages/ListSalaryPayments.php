<?php

namespace App\Filament\Admin\Resources\SalaryPayments\Pages;

use App\Filament\Admin\Resources\SalaryPayments\SalaryPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaryPayments extends ListRecords
{
    protected static string $resource = SalaryPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة راتب جديد'),
        ];
    }
}
