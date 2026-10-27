<?php

namespace App\Filament\Admin\Resources\InstructorEvaluationAppeals\Pages;

use App\Filament\Admin\Resources\InstructorEvaluationAppeals\AdminInstructorEvaluationAppealResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAdminInstructorEvaluationAppeal extends ViewRecord
{
    protected static string $resource = AdminInstructorEvaluationAppealResource::class;

    public function getTitle(): string
    {
        return "معاينة الاعتراض على تقييم: {$this->record->evaluation?->evaluation_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('decide_appeal')
                ->label('إصدار قرار الإدارة النهائي')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn() => in_array($this->record->status, ['submitted', 'supervisor_responded', 'returned_for_clarification']))
                ->form([
                    Select::make('decision')
                        ->label('القرار النهائي لإدارة المدرسة')
                        ->options([
                            'accepted' => 'قبول الاعتراض وتفويض المشرف بالتعديل',
                            'rejected' => 'رفض الاعتراض وتثبيت التقييم الأصلي',
                            'returned_for_clarification' => 'إعادة للمشرف لطلب استيضاح إضافي',
                        ])
                        ->required(),

                    Textarea::make('decision_reason')
                        ->label('أسباب ومسوغات القرار (إلزامي للتوثيق الإداري)')
                        ->placeholder('أدخل أسباب القرار بناء على فحص التقييم والشواهد...')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->decideByManagement(
                        auth()->user(),
                        $data['decision'],
                        $data['decision_reason']
                    );

                    Notification::make()
                        ->title('تم اعتماد قرار الإدارة النهائي بنجاح')
                        ->body("تم توثيق القرار في السجل التاريخي وحفظ اللقطة الزمنية للتقييم.")
                        ->success()
                        ->send();

                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الاعتراض والتقييم المتنازع عليه')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('evaluation.evaluation_code')
                            ->label('كود التقييم')
                            ->weight('bold'),

                        TextEntry::make('instructor.user.name')
                            ->label('المهندس المعترض')
                            ->weight('bold'),

                        TextEntry::make('evaluation.supervisor.name')
                            ->label('المشرف التعليمي المقيّم'),

                        TextEntry::make('status')
                            ->label('حالة الاعتراض')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'accepted' => 'مقبول ومعتمد للتعديل',
                                'rejected' => 'مرفوض بقرار الإدارة',
                                'returned_for_clarification' => 'معاد للاستيضاح',
                                'supervisor_responded' => 'رد المشرف - بانتظار قرار الإدارة',
                                default => 'جديد وبانتظار رد المشرف',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'accepted' => 'success',
                                'rejected' => 'danger',
                                'returned_for_clarification' => 'warning',
                                'supervisor_responded' => 'info',
                                default => 'gray',
                            }),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('evaluation.evaluation_period')
                            ->label('فترة التقييم')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('evaluation.calculated_average')
                            ->label('المتوسط المحسوب')
                            ->badge()
                            ->formatStateUsing(fn($state) => "⭐ {$state} / 5.00"),

                        TextEntry::make('created_at')
                            ->label('تاريخ تقديم الاعتراض')
                            ->dateTime('Y-m-d H:i'),
                    ]),
                ]),

            Section::make('بيان اعتراض المهندس')
                ->schema([
                    TextEntry::make('reason')
                        ->label('أسباب ومبررات المهندس')
                        ->columnSpanFull(),
                ]),

            Section::make('رد المشرف التعليمي وتوصيته للإدارة')
                ->schema([
                    TextEntry::make('supervisor_response')
                        ->label('رد المشرف التعليمي')
                        ->default('لم يقم المشرف بتسجيل رد رسمي بعد')
                        ->columnSpanFull(),

                    TextEntry::make('supervisor_responded_at')
                        ->label('تاريخ رد المشرف')
                        ->dateTime('Y-m-d H:i')
                        ->default('—'),
                ]),

            Section::make('قرار إدارة المدرسة النهائي والمسوغات')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('management_decision')
                            ->label('القرار النهائي')
                            ->badge()
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'accepted' => 'قبول الاعتراض',
                                'rejected' => 'رفض الاعتراض',
                                'returned_for_clarification' => 'طلب استيضاح',
                                default => 'قيد النظر الإداري',
                            })
                            ->color(fn(?string $state): string => match ($state) {
                                'accepted' => 'success',
                                'rejected' => 'danger',
                                'returned_for_clarification' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('decidedByUser.name')
                            ->label('المسئول متخذ القرار')
                            ->default('—'),

                        TextEntry::make('decided_at')
                            ->label('تاريخ اتخاذ القرار')
                            ->dateTime('Y-m-d H:i')
                            ->default('—'),
                    ]),

                    TextEntry::make('management_decision_reason')
                        ->label('أسباب ومسوغات قرار الإدارة')
                        ->default('بانتظار بت الإدارة')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
