<?php

namespace App\Filament\Admin\Resources\OverdueInstallmentResource\Pages;

use App\Filament\Admin\Resources\OverdueInstallmentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListOverdueInstallments extends ListRecords
{
    protected static string $resource = OverdueInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('تصدير التقرير')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // يمكن إضافة وظيفة التصدير لاحقاً
                    \Filament\Notifications\Notification::make()
                        ->info()
                        ->title('قريباً')
                        ->body('سيتم إضافة وظيفة التصدير قريباً')
                        ->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'تقرير الأقساط المتأخرة';
    }
}
