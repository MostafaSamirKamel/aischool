<?php

namespace App\Filament\Supervisor\Resources\SupervisionReports\Pages;

use App\Filament\Supervisor\Resources\SupervisionReports\SupervisionReportResource;
use App\Services\EducationalSupervisionService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSupervisionReports extends ListRecords
{
    protected static string $resource = SupervisionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_weekly_draft')
                ->label('تجهيز مسودة الأسبوع الحالي تلقائياً')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->action(function () {
                    $service = app(EducationalSupervisionService::class);
                    $user = auth()->user();
                    $weekKey = now()->format('o-\\WW');

                    $report = $service->generateWeeklyReportDraft($user->tenant_id ?? 1, $weekKey, $user);

                    Notification::make()
                        ->title('تم تجهيز مسودة التقرير الأسبوعي')
                        ->body("كود التقرير: {$report->report_code}. يمكنك الآن مراجعة الإحصائيات وإضافة الملاحظات.")
                        ->success()
                        ->send();

                    $this->redirect(SupervisionReportResource::getUrl('view', ['record' => $report]));
                }),

            Action::make('generate_monthly_draft')
                ->label('تجهيز مسودة الشهر الحالي تلقائياً')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->action(function () {
                    $service = app(EducationalSupervisionService::class);
                    $user = auth()->user();
                    $monthKey = now()->format('Y-m');

                    $report = $service->generateMonthlyReportDraft($user->tenant_id ?? 1, $monthKey, $user);

                    Notification::make()
                        ->title('تم تجهيز مسودة التقرير الشهري')
                        ->body("كود التقرير: {$report->report_code}. يمكنك مراجعة البيانات واعتماد النشر.")
                        ->success()
                        ->send();

                    $this->redirect(SupervisionReportResource::getUrl('view', ['record' => $report]));
                }),

            CreateAction::make()
                ->label('إنشاء تقرير يدوي')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
