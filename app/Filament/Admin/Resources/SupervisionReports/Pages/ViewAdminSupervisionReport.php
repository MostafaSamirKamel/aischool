<?php

namespace App\Filament\Admin\Resources\SupervisionReports\Pages;

use App\Filament\Admin\Resources\SupervisionReports\AdminSupervisionReportResource;
use App\Models\SupervisionReport;
use App\Services\EducationalSupervisionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAdminSupervisionReport extends ViewRecord
{
    protected static string $resource = AdminSupervisionReportResource::class;

    public function getTitle(): string
    {
        return "معاينة التقرير الإشرافي للفرع: {$this->record->report_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_comment')
                ->label('إضافة ملاحظة إدارية')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('info')
                ->form([
                    Textarea::make('comment')
                        ->label('الملاحظة الإدارية')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $service = app(EducationalSupervisionService::class);
                    $service->addManagementComment($this->record, auth()->user(), $data['comment']);

                    Notification::make()
                        ->title('تم حفظ الملاحظة الإدارية بنجاح')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Action::make('request_clarification')
                ->label('طلب استفسار من المشرف')
                ->icon('heroicon-o-question-mark-circle')
                ->color('warning')
                ->form([
                    Textarea::make('question')
                        ->label('الاستفسار المطلوب توضيحه من المشرف')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $service = app(EducationalSupervisionService::class);
                    $service->requestReportClarification($this->record, auth()->user(), $data['question']);

                    Notification::make()
                        ->title('تم إرسال طلب التوضيح للمشرف')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Action::make('request_revision')
                ->label('طلب إعادة صياغة / تعديل')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('طلب تعديل التقرير')
                ->modalDescription('سيتم حفظ لقطة الإصدار الحالي وإشعار المشرف لإعداد مسودة جديدة منقحة.')
                ->form([
                    Textarea::make('reason')
                        ->label('أسباب ومبررات طلب التعديل (إلزامي)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $service = app(EducationalSupervisionService::class);
                    $service->requestReportRevision($this->record, auth()->user(), $data['reason']);

                    Notification::make()
                        ->title('تم طلب تعديل التقرير وإشعار المشرف')
                        ->warning()
                        ->send();

                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات التقرير والفرع')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('tenant.name')
                            ->label('الفرع')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('report_code')
                            ->label('كود التقرير')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('report_type')
                            ->label('نوع التقرير')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                SupervisionReport::TYPE_WEEKLY => 'تقرير إشرافي أسبوعي',
                                default => 'تقرير إداري تعليمي شهري',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                SupervisionReport::TYPE_WEEKLY => 'info',
                                default => 'primary',
                            }),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                SupervisionReport::STATUS_PUBLISHED => 'معتمد ومنشور',
                                SupervisionReport::STATUS_REVISION_REQUESTED => 'مطلوب مراجعة وتعديل',
                                default => 'مسودة',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                SupervisionReport::STATUS_PUBLISHED => 'success',
                                SupervisionReport::STATUS_REVISION_REQUESTED => 'danger',
                                default => 'warning',
                            }),
                    ]),

                    Grid::make(4)->schema([
                        TextEntry::make('version')
                            ->label('الإصدار')
                            ->formatStateUsing(fn($state) => "v{$state}"),

                        TextEntry::make('period_key')
                            ->label('الفترة'),

                        TextEntry::make('publisher.name')
                            ->label('المشرف المعتمد الناشر'),

                        TextEntry::make('published_at')
                            ->label('توقيت النشر')
                            ->dateTime('Y-m-d H:i'),
                    ]),
                ]),

            Section::make('المؤشرات التشغيلية للفترة (Branch Metrics Snapshot)')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('metrics_snapshot.scheduled_sessions')
                            ->label('الحصص المجدولة')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('metrics_snapshot.completed_sessions')
                            ->label('الحصص المنفذة')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('metrics_snapshot.missing_attendance')
                            ->label('حصص لم يُسجل حضورها')
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('metrics_snapshot.confirmed_absences')
                            ->label('غياب الطلاب المؤكد')
                            ->badge()
                            ->color('warning'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('metrics_snapshot.homework_awaiting_grading')
                            ->label('واجبات تنتظر التصحيح')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('metrics_snapshot.new_observations_count')
                            ->label('توجيهات إشرافية جديدة')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('metrics_snapshot.completed_observations_count')
                            ->label('توجيهات منجزة')
                            ->badge()
                            ->color('success'),
                    ]),
                ]),

            Section::make('تحليل وتوصيات المشرف التعليمي')
                ->schema([
                    TextEntry::make('summary')
                        ->label('الملخص العام')
                        ->columnSpanFull(),

                    TextEntry::make('instructor_observations')
                        ->label('ملاحظات أداء المهندسين')
                        ->columnSpanFull(),

                    TextEntry::make('group_observations')
                        ->label('ملاحظات المجموعات وسير الدراسة')
                        ->columnSpanFull(),

                    TextEntry::make('problems_encountered')
                        ->label('المشكلات والعقبات')
                        ->default('لا توجد مشكلات استثنائية مسجلة')
                        ->columnSpanFull(),

                    TextEntry::make('recommendations')
                        ->label('التوصيات المقترحة للإدارة')
                        ->color('primary')
                        ->columnSpanFull(),
                ]),

            Section::make('سجل استفسارات وملاحظات الإدارة')
                ->schema([
                    RepeatableEntry::make('management_comments')
                        ->label('الملاحظات الإدارية المسجلة')
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('comment')
                                    ->label('نص الملاحظة'),
                                TextEntry::make('added_at')
                                    ->label('التوقيت'),
                            ]),
                        ]),

                    RepeatableEntry::make('clarification_requests')
                        ->label('طلبات الاستفسار والردود')
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('question')
                                    ->label('استفسار الإدارة')
                                    ->weight('bold'),
                                TextEntry::make('requested_at')
                                    ->label('توقيت الاستفسار'),
                                TextEntry::make('response')
                                    ->label('رد المشرف')
                                    ->default('بانتظار رد المشرف')
                                    ->color('info'),
                                TextEntry::make('responded_at')
                                    ->label('توقيت الرد')
                                    ->default('—'),
                            ]),
                        ]),
                ]),

            Section::make('سجل إصدارات التقرير التاريخية (Historical Versions)')
                ->schema([
                    RepeatableEntry::make('versions')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('version')
                                    ->label('الإصدار')
                                    ->formatStateUsing(fn($state) => "الإصدار v{$state}")
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('created_at')
                                    ->label('توقيت الحفظ')
                                    ->dateTime('Y-m-d H:i'),

                                TextEntry::make('change_reason')
                                    ->label('سبب الحفظ / التعديل')
                                    ->default('نشر أولي'),
                            ]),
                        ]),
                ]),
        ]);
    }
}
