<?php

namespace App\Filament\Admin\Resources\OverdueInstallmentResource\Pages;

use App\Filament\Admin\Resources\OverdueInstallmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOverdueInstallment extends EditRecord
{
    protected static string $resource = OverdueInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'تحديث القسط المتأخر';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
