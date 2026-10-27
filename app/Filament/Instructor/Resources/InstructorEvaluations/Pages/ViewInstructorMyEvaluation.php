<?php

namespace App\Filament\Instructor\Resources\InstructorEvaluations\Pages;

use App\Filament\Instructor\Resources\InstructorEvaluations\InstructorMyEvaluationResource;
use App\Models\Instructor;
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

class ViewInstructorMyEvaluation extends ViewRecord
{
    protected static string $resource = InstructorMyEvaluationResource::class;

    public function getTitle(): string
    {
        return "تقييمي التعليمي الرسمي: {$this->record->evaluation_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_comment')
                ->label('إضافة تعقيب / توضيح')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->color('info')
                ->form([
                    Textarea::make('comment')
                        ->label('نص التعقيب والتوضيح (لا يعتبر اعتراضاً رسمياً ولا يغير الدرجة)')
                        ->placeholder('أدخل توضيحك أو تعقيبك حول النقاط الواردة بالتقييم...')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $instructor = Instructor::where('user_id', auth()->id())->first();

                    $this->record->appeals()->create([
                        'tenant_id' => $this->record->tenant_id,
                        'evaluation_version' => $this->record->version,
                        'instructor_id' => $instructor?->id ?? 0,
                        'type' => 'comment',
                        'reason' => trim($data['comment']),
                        'status' => 'submitted',
                    ]);

                    Notification::make()
                        ->title('تمت إضافة تعقيبك بنجاح')
                        ->body('تم حفظ التعقيب وإرفاقه بسجل التقييم.')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Action::make('submit_formal_appeal')
                ->label('تقديم اعتراض رسمي للإدارة')
                ->icon('heroicon-o-shield-exclamation')
                ->color('danger')
                ->modalHeading('تقديم اعتراض رسمي إلى إدارة المدرسة')
                ->modalDescription('سيتم توجيه الاعتراض رسمياً إلى المشرف للرد عليه، ثم يُرفع للإدارة للبت النهائي فيه وفق الصلاحيات المعتمدة.')
                ->form([
                    Textarea::make('appeal_reason')
                        ->label('أسباب ومبررات الاعتراض الرسمي بالتفصيل (إلزامي)')
                        ->placeholder('بيّن بدقة أسباب الاعتراض على التقييم والشواهد المؤيدة...')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $instructor = Instructor::where('user_id', auth()->id())->first();

                    $this->record->appeals()->create([
                        'tenant_id' => $this->record->tenant_id,
                        'evaluation_version' => $this->record->version,
                        'instructor_id' => $instructor?->id ?? 0,
                        'type' => 'appeal',
                        'reason' => trim($data['appeal_reason']),
                        'status' => 'submitted',
                    ]);

                    Notification::make()
                        ->title('تم تسجيل الاعتراض الرسمي بنجاح')
                        ->body('تم رفع الاعتراض وسيقوم المشرف بالرد عليه تمهيداً لبت الإدارة النهائي.')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات التقييم المنشور')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('evaluation_code')
                            ->label('كود التقييم')
                            ->weight('bold'),

                        TextEntry::make('evaluation_period')
                            ->label('الفترة')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('type_label')
                            ->label('نوع التقييم')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('published_at')
                            ->label('تاريخ النشر')
                            ->dateTime('Y-m-d H:i'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('supervisor.name')
                            ->label('المشرف التعليمي المقيّم'),

                        TextEntry::make('review_basis_label')
                            ->label('أساس التقييم'),
                    ]),
                ]),

            Section::make('درجات المعايير الستة المعتمدة')
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
                            ->label('المتوسط الحسابي للمعايير المقيمة')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state ? "⭐ {$state} / 5.00" : 'لم يُحسب لعدم وجود معايير مقيمة')
                            ->color(fn($state) => match (true) {
                                $state >= 4.0 => 'success',
                                $state >= 3.0 => 'warning',
                                $state > 0 => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('criteria_observed_count')
                            ->label('المعايير المعتمدة في الحساب')
                            ->formatStateUsing(fn($state) => "{$state} معايير من 6"),
                    ]),
                ]),

            Section::make('الملاحظات والتوجيهات الإشرافية المعتمدة')
                ->schema([
                    TextEntry::make('strengths')
                        ->label('نقاط القوة')
                        ->color('success'),

                    TextEntry::make('areas_for_improvement')
                        ->label('نقاط الضعف / جوانب التحسين المطلوبة')
                        ->color('warning'),

                    TextEntry::make('recommendations')
                        ->label('التوصيات المقترحة للتطوير')
                        ->color('primary'),
                ]),

            Section::make('تعقيباتك واعتراضاتك الرسمية المسجلة')
                ->schema([
                    RepeatableEntry::make('appeals')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('type')
                                    ->label('النوع')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'appeal' => 'اعتراض رسمي',
                                        default => 'تعقيب / توضيح',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'appeal' => 'danger',
                                        default => 'info',
                                    }),

                                TextEntry::make('status')
                                    ->label('حالة النظر')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'accepted' => 'مقبول من الإدارة',
                                        'rejected' => 'مرفوض من الإدارة',
                                        'supervisor_responded' => 'رد المشرف - بانتظار قرار الإدارة',
                                        default => 'مقدم وبانتظار رد المشرف',
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
                                ->label('بيانك')
                                ->columnSpanFull(),

                            TextEntry::make('supervisor_response')
                                ->label('رد المشرف التعليمي')
                                ->default('لم يرد المشرف بعد')
                                ->columnSpanFull(),

                            TextEntry::make('management_decision_reason')
                                ->label('قرار إدارة المدرسة النهائي')
                                ->default('بانتظار بت الإدارة')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
