<?php

namespace App\Filament\Supervisor\Resources\InstructorEvaluations\Pages;

use App\Filament\Supervisor\Resources\InstructorEvaluations\InstructorEvaluationResource;
use App\Models\InstructorEvaluation;
use App\Models\InstructorEvaluationAppeal;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewInstructorEvaluation extends ViewRecord
{
    protected static string $resource = InstructorEvaluationResource::class;

    public function getTitle(): string
    {
        return "معاينة التقييم التعليمي: {$this->record->evaluation_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish_evaluation')
                ->label('نشر التقييم الرسمي للمهندس')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn() => $this->record->status === InstructorEvaluation::STATUS_DRAFT)
                ->requiresConfirmation()
                ->modalHeading('تأكيد نشر التقييم التعليمي')
                ->modalDescription('سيتم نشر التقييم في حساب المهندس وإرسال إشعار فوري له.')
                ->action(function () {
                    $this->record->publish(auth()->user());
                    Notification::make()
                        ->title('تم نشر التقييم الرسمي بنجاح')
                        ->body("أصبح التقييم {$this->record->evaluation_code} متاحاً للمهندس.")
                        ->success()
                        ->send();
                    $this->fillForm();
                }),

            Action::make('respond_to_appeal')
                ->label('الرد على اعتراض المهندس')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('warning')
                ->visible(function () {
                    return $this->record->appeals()
                        ->where('type', 'appeal')
                        ->whereNull('supervisor_responded_at')
                        ->exists();
                })
                ->form([
                    Textarea::make('supervisor_response')
                        ->label('رد المشرف التعليمي وتوصيته للإدارة (إلزامي)')
                        ->placeholder('أدخل توضيحك الإشرافي حول حيثيات التقييم والمرئيات لترفع للإدارة للبت فيها...')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $appeal = $this->record->appeals()
                        ->where('type', 'appeal')
                        ->whereNull('supervisor_responded_at')
                        ->first();

                    if ($appeal) {
                        $appeal->update([
                            'supervisor_response' => trim($data['supervisor_response']),
                            'supervisor_responded_at' => now(),
                            'supervisor_id' => auth()->id(),
                            'status' => 'supervisor_responded',
                        ]);

                        Notification::make()
                            ->title('تم تسجيل رد المشرف على الاعتراض')
                            ->body('تم حفظ الرد وتجهيز ملف الاعتراض لعرضه على الإدارة لاتخاذ القرار النهائي.')
                            ->success()
                            ->send();

                        $this->fillForm();
                    }
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية للتقييم')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('evaluation_code')
                            ->label('كود التقييم')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                InstructorEvaluation::STATUS_PUBLISHED => 'منشور رسمي',
                                default => 'مسودة غير منشورة',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                InstructorEvaluation::STATUS_PUBLISHED => 'success',
                                default => 'gray',
                            }),

                        TextEntry::make('type_label')
                            ->label('نوع التقييم')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('evaluation_period')
                            ->label('فترة التقييم')
                            ->badge()
                            ->color('gray'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('instructor.user.name')
                            ->label('المهندس الخاضع للتقييم')
                            ->weight('bold'),

                        TextEntry::make('supervisor.name')
                            ->label('المشرف القائم بالتقييم'),

                        TextEntry::make('review_basis_label')
                            ->label('أساس وبينة التقييم'),
                    ]),
                ]),

            Section::make('المعايير الرقمية الستة والمتوسط الحسابي')
                ->description('المعايير غير المرصودة تُستثنى من الحساب ولا تُمنح صفراً.')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('score_explanation_quality')
                            ->label('1. جودة الشرح وتبسيط المفاهيم')
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} من 5" : 'لم يتم تقييم هذا المعيار')
                            ->color(fn($state) => $state ? 'primary' : 'gray'),

                        TextEntry::make('score_student_interaction')
                            ->label('2. التفاعل وإشراك الطلاب')
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} من 5" : 'لم يتم تقييم هذا المعيار')
                            ->color(fn($state) => $state ? 'primary' : 'gray'),

                        TextEntry::make('score_practical_application')
                            ->label('3. التطبيق العملي والتمارين')
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} من 5" : 'لم يتم تقييم هذا المعيار')
                            ->color(fn($state) => $state ? 'primary' : 'gray'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('score_curriculum_adherence')
                            ->label('4. الالتزام بالمنهج والخطة')
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} من 5" : 'لم يتم تقييم هذا المعيار')
                            ->color(fn($state) => $state ? 'primary' : 'gray'),

                        TextEntry::make('score_time_management')
                            ->label('5. إدارة وقت الحصة والأنشطة')
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} من 5" : 'لم يتم تقييم هذا المعيار')
                            ->color(fn($state) => $state ? 'primary' : 'gray'),

                        TextEntry::make('score_homework_followup')
                            ->label('6. متابعة وتصحيح الواجبات')
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} من 5" : 'لم يتم تقييم هذا المعيار')
                            ->color(fn($state) => $state ? 'primary' : 'gray'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('calculated_average')
                            ->label('المتوسط الحسابي للمعايير المرصودة')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state ? "{$state} / 5.00" : 'لم يتم تقييم معايير لحساب المتوسط')
                            ->color(fn($state) => match (true) {
                                $state >= 4.0 => 'success',
                                $state >= 3.0 => 'warning',
                                $state > 0 => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('criteria_observed_count')
                            ->label('عدد المعايير المرصودة فعلياً')
                            ->formatStateUsing(fn($state) => "{$state} معايير تم تقييمها من أصل 6"),
                    ]),
                ]),

            Section::make('الأقسام الوصفية والتحليلية')
                ->schema([
                    TextEntry::make('strengths')
                        ->label('نقاط القوة')
                        ->color('success'),

                    TextEntry::make('areas_for_improvement')
                        ->label('نقاط الضعف / جوانب التحسين')
                        ->color('warning'),

                    TextEntry::make('recommendations')
                        ->label('التوصيات والمقترحات العملية')
                        ->color('primary'),
                ]),

            Section::make('التعقيبات والاعتراضات الرسمية المقدمة من المهندس')
                ->schema([
                    RepeatableEntry::make('appeals')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('type')
                                    ->label('النوع')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'appeal' => 'اعتراض رسمي للإدارة',
                                        default => 'تعقيب / توضيح',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'appeal' => 'danger',
                                        default => 'info',
                                    }),

                                TextEntry::make('status')
                                    ->label('حالة الاعتراض')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'accepted' => 'مقبول من الإدارة',
                                        'rejected' => 'مرفوض من الإدارة',
                                        'supervisor_responded' => 'بانتظار قرار الإدارة',
                                        default => 'مقدم من المهندس',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'accepted' => 'success',
                                        'rejected' => 'danger',
                                        'supervisor_responded' => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('created_at')
                                    ->label('تاريخ التقديم')
                                    ->dateTime('Y-m-d H:i'),
                            ]),

                            TextEntry::make('reason')
                                ->label('بيان المهندس / سبب الاعتراض')
                                ->columnSpanFull(),

                            TextEntry::make('supervisor_response')
                                ->label('رد المشرف التعليمي وتوصيته')
                                ->default('لم يسجل المشرف رداً بعد')
                                ->columnSpanFull(),

                            TextEntry::make('management_decision_reason')
                                ->label('قرار الإدارة وأسبابه')
                                ->default('بانتظار بت الإدارة النهائي')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
