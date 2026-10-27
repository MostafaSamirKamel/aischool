<?php

namespace App\Filament\Supervisor\Resources\SupervisionReports\Pages;

use App\Filament\Supervisor\Resources\SupervisionReports\SupervisionReportResource;
use App\Models\SupervisionReport;
use App\Services\EducationalSupervisionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewSupervisionReport extends ViewRecord
{
    protected static string $resource = SupervisionReportResource::class;

    public function getTitle(): string
    {
        return "معاينة التقرير الإشرافي: {$this->record->report_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('تعديل مسودة التقرير')
                ->visible(fn() => in_array($this->record->status, [SupervisionReport::STATUS_DRAFT, SupervisionReport::STATUS_REVISION_REQUESTED])),

            Action::make('publish_report')
                ->label('اعتماد ونشر التقرير للإدارة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => in_array($this->record->status, [SupervisionReport::STATUS_DRAFT, SupervisionReport::STATUS_REVISION_REQUESTED]))
                ->requiresConfirmation()
                ->modalHeading('تأكيد نشر التقرير للإدارة')
                ->modalDescription('سيتم حفظ لقطة تاريخية للإصدار الحالي ونشر التقرير لإدارة المدرسة.')
                ->action(function () {
                    $service = app(EducationalSupervisionService::class);
                    $service->publishSupervisionReport($this->record, auth()->user());

                    Notification::make()
                        ->title('تم اعتماد ونشر التقرير للإدارة بنجاح')
                        ->body("أصبح التقرير {$this->record->report_code} منشوراً ومعتمداً.")
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Action::make('respond_to_clarification')
                ->label('الرد على استفسار الإدارة')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->color('warning')
                ->visible(function () {
                    $requests = $this->record->clarification_requests ?? [];
                    return !empty($requests) && collect($requests)->contains('response', null);
                })
                ->form([
                    Textarea::make('response')
                        ->label('توضيح المشرف وردّه على الإدارة (إلزامي)')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $service = app(EducationalSupervisionService::class);
                    $service->respondToClarification($this->record, auth()->user(), $data['response']);

                    Notification::make()
                        ->title('تم إرسال التوضيح للإدارة بنجاح')
                        ->body('تم حفظ رد المشرف وتوثيقه في ملف التقرير.')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات التقرير وحالة الاعتماد')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('report_code')
                            ->label('كود التقرير')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('report_type')
                            ->label('النوع')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                SupervisionReport::TYPE_WEEKLY => 'تقرير إشرافي أسبوعي',
                                default => 'تقرير إداري تعليمي شهري',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                SupervisionReport::TYPE_WEEKLY => 'info',
                                default => 'primary',
                            }),

                        TextEntry::make('period_key')
                            ->label('فترة التقرير')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('status')
                            ->label('حالة النشر')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                SupervisionReport::STATUS_PUBLISHED => 'منشور ومعتمد للإدارة',
                                SupervisionReport::STATUS_REVISION_REQUESTED => 'مطلوب مراجعة وتعديل',
                                default => 'مسودة قيد الإعداد',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                SupervisionReport::STATUS_PUBLISHED => 'success',
                                SupervisionReport::STATUS_REVISION_REQUESTED => 'danger',
                                default => 'warning',
                            }),
                    ]),

                    Grid::make(4)->schema([
                        TextEntry::make('version')
                            ->label('رقم الإصدار')
                            ->formatStateUsing(fn($state) => "الإصدار {$state}")
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('period_start')
                            ->label('بداية الفترة')
                            ->date('Y-m-d'),

                        TextEntry::make('period_end')
                            ->label('نهاية الفترة')
                            ->date('Y-m-d'),

                        TextEntry::make('published_at')
                            ->label('تاريخ النشر والاعتماد')
                            ->dateTime('Y-m-d H:i')
                            ->default('لم يُنشر بعد'),
                    ]),
                ]),

            Section::make('المؤشرات التشغيلية التلقائية للفترة (Real Branch Metrics Snapshot)')
                ->description('إحصائيات موثقة مستمدة مباشرة من واقع سجلات الفرع بدون تكرار أو حسابات يدوية.')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('metrics_snapshot.scheduled_sessions')
                            ->label('الحصص المجدولة')
                            ->default(0)
                            ->badge()
                            ->color('info'),

                        TextEntry::make('metrics_snapshot.completed_sessions')
                            ->label('الحصص المنفذة')
                            ->default(0)
                            ->badge()
                            ->color('success'),

                        TextEntry::make('metrics_snapshot.missing_attendance')
                            ->label('حصص لم يُسجل حضورها')
                            ->default(0)
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('metrics_snapshot.confirmed_absences')
                            ->label('غياب الطلاب المؤكد')
                            ->default(0)
                            ->badge()
                            ->color('warning'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('metrics_snapshot.homework_awaiting_grading')
                            ->label('واجبات تنتظر التصحيح')
                            ->default(0)
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('metrics_snapshot.new_observations_count')
                            ->label('توجيهات إشرافية جديدة')
                            ->default(0)
                            ->badge()
                            ->color('info'),

                        TextEntry::make('metrics_snapshot.completed_observations_count')
                            ->label('توجيهات إشرافية منجزة')
                            ->default(0)
                            ->badge()
                            ->color('success'),
                    ]),
                ]),

            Section::make('التحليل والأقسام الكتابية المعتمدة من المشرف')
                ->schema([
                    TextEntry::make('summary')
                        ->label('الملخص الإشرافي العام')
                        ->columnSpanFull(),

                    TextEntry::make('instructor_observations')
                        ->label('ملاحظات أداء والتزام المهندسين')
                        ->columnSpanFull(),

                    TextEntry::make('group_observations')
                        ->label('ملاحظات المجموعات الأكاديمية وسير الدراسة')
                        ->columnSpanFull(),

                    TextEntry::make('problems_encountered')
                        ->label('المشكلات والتحديات المرصودة')
                        ->default('لا توجد مشكلات استثنائية مسجلة')
                        ->columnSpanFull(),

                    TextEntry::make('actions_taken')
                        ->label('الإجراءات الإشرافية والتصحيحية المتخذة')
                        ->default('تم المتابعة والتوجيه الميداني')
                        ->columnSpanFull(),

                    TextEntry::make('recommendations')
                        ->label('التوصيات المقترحة للإدارة')
                        ->color('primary')
                        ->columnSpanFull(),
                ]),

            Section::make('تواصل الإدارة وطلبات المراجعة والتوضيح')
                ->schema([
                    TextEntry::make('revision_reason')
                        ->label('سبب طلب إعادة الصياغة / التعديل من الإدارة')
                        ->default('لا توجد طلبات تعديل')
                        ->color('danger')
                        ->columnSpanFull(),

                    RepeatableEntry::make('clarification_requests')
                        ->label('سجل استفسارات وتوضيحات الإدارة')
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('requested_at')
                                    ->label('توقيت الاستفسار'),

                                TextEntry::make('question')
                                    ->label('استفسار الإدارة')
                                    ->weight('bold'),

                                TextEntry::make('responded_at')
                                    ->label('توقيت الرد')
                                    ->default('بانتظار رد المشرف'),

                                TextEntry::make('response')
                                    ->label('توضيح ورد المشرف')
                                    ->default('لم يتم الرد بعد')
                                    ->color('info'),
                            ]),
                        ]),
                ]),

            Section::make('سجل إصدارات التقرير التاريخية (Preserved Historical Versions)')
                ->description('كافة النسخ المنشورة سابقاً محفوظة برقم الإصدار وسبب التعديل لضمان الشفافية التامة.')
                ->schema([
                    RepeatableEntry::make('versions')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('version')
                                    ->label('رقم الإصدار')
                                    ->formatStateUsing(fn($state) => "الإصدار v{$state}")
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('created_at')
                                    ->label('تاريخ وتوقيت الأرشفة')
                                    ->dateTime('Y-m-d H:i'),

                                TextEntry::make('change_reason')
                                    ->label('سبب حفظ الإصدار')
                                    ->default('نشر أولي معتمد'),
                            ]),
                        ]),
                ]),
        ]);
    }
}
