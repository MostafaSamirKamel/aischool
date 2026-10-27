<?php

namespace App\Filament\Admin\Resources\Payments\Pages;

use App\Filament\Admin\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        
        if (!auth()->user()->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        $actions[] = \Filament\Actions\Action::make('collection_reports')
            ->label('تقارير التحصيلات')
            ->icon('heroicon-o-presentation-chart-line')
            ->color('gray')
            ->url('/admin/reporting-center-page?section=collections');

        return $actions;
    }

    public function getTitle(): string
    {
        return __('filament/admin/list_payments.title');
    }

    public function getHeading(): string
    {
        return __('filament/admin/list_payments.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_payments.title');
    }

}
