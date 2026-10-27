<?php

namespace App\Filament\Admin\Resources\Exams\Pages;

use App\Filament\Admin\Resources\Exams\ExamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة امتحانات
        if (!$user->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        $actions[] = \Filament\Actions\Action::make('exam_reports')
            ->label('تقارير الامتحانات')
            ->icon('heroicon-o-presentation-chart-line')
            ->color('gray')
            ->url('/admin/reporting-center-page?section=academic');

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_exams.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_exams.title');
    }

}
