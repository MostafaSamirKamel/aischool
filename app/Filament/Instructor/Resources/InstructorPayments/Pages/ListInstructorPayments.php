<?php

namespace App\Filament\Instructor\Resources\InstructorPayments\Pages;

use App\Filament\Instructor\Resources\InstructorPayments\InstructorPaymentResource;
use App\Filament\Instructor\Widgets\InstructorPaymentStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListInstructorPayments extends ListRecords
{
    protected static string $resource = InstructorPaymentResource::class;

    public function getTitle(): string
    {
        return __('filament/instructor/list_instructor_payments.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/list_instructor_payments.title');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InstructorPaymentStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

}
