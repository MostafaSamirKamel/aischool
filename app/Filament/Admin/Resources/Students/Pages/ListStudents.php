<?php

namespace App\Filament\Admin\Resources\Students\Pages;

use App\Filament\Admin\Resources\Students\StudentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (!auth()->user()->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        $actions[] = \Filament\Actions\Action::make('student_reports')
            ->label('تقارير الطلاب')
            ->icon('heroicon-o-presentation-chart-line')
            ->color('gray')
            ->url('/admin/reporting-center-page?section=growth');

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_students.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_students.title');
    }

}
