<?php

namespace App\Filament\Supervisor\Resources\SupervisoryObservations\Pages;

use App\Filament\Supervisor\Resources\SupervisoryObservations\SupervisoryObservationResource;
use App\Models\SupervisoryObservation;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewSupervisoryObservation extends ViewRecord
{
    protected static string $resource = SupervisoryObservationResource::class;

    public function getTitle(): string
    {
        return "معاينة الملاحظة الإشرافية: {$this->record->observation_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_completion')
                ->label('اعتماد الإغلاق بنجاح')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn() => $this->record->status === SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
                ->form([
                    Textarea::make('verification_notes')
                        ->label('ملاحظات التحقق والاعتماد (اختياري)')
                        ->placeholder('أدخل ملاحظاتك الإشرافية للتأكد من اكتمال العمل...')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->approveCompletion(auth()->user(), $data['verification_notes'] ?? null);
                    Notification::make()
                        ->title('تم اعتماد إغلاق الملاحظة بنجاح')
                        ->body("أصبحت حالة الملاحظة {$this->record->observation_code} مكتملة.")
                        ->success()
                        ->send();
                    $this->fillForm();
                }),

            Action::make('return_for_correction')
                ->label('إعادة للمهندس للتصحيح')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn() => $this->record->status === SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
                ->form([
                    Textarea::make('mandatory_reason')
                        ->label('سبب الإعادة والتوجيه الإضافي (إلزامي)')
                        ->placeholder('وضّح للمهندس بدقة ما يجب تعديله أو استكماله...')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->returnForCorrection(auth()->user(), $data['mandatory_reason']);
                    Notification::make()
                        ->title('تمت إعادة الملاحظة للمهندس للتصحيح')
                        ->body("عادت حالة الملاحظة {$this->record->observation_code} إلى 'جديدة'.")
                        ->warning()
                        ->send();
                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية للملاحظة')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('observation_code')
                            ->label('كود الملاحظة')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('الحالة الحالية')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
                                SupervisoryObservation::STATUS_COMPLETED => 'مكتملة',
                                default => 'جديدة',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'warning',
                                SupervisoryObservation::STATUS_COMPLETED => 'success',
                                default => 'info',
                            }),

                        TextEntry::make('priority')
                            ->label('الأولوية')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'urgent' => 'عاجلة',
                                'low' => 'منخفضة',
                                default => 'عادية',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'urgent' => 'danger',
                                'low' => 'gray',
                                default => 'warning',
                            }),

                        TextEntry::make('category_label')
                            ->label('التصنيف')
                            ->badge()
                            ->color('gray'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('instructor.user.name')
                            ->label('المهندس المسئول'),

                        TextEntry::make('group.name')
                            ->label('المجموعة')
                            ->default('—'),

                        TextEntry::make('expected_resolution_date')
                            ->label('مهلة المعالجة')
                            ->date('Y-m-d'),
                    ]),

                    TextEntry::make('title')
                        ->label('الموضوع')
                        ->weight('bold'),

                    TextEntry::make('description')
                        ->label('تفاصيل الملاحظة المرصودة'),

                    TextEntry::make('required_action')
                        ->label('الإجراء التصحيحي المطلوب من المهندس')
                        ->color('primary')
                        ->weight('bold'),
                ]),

            Section::make('رد المهندس وإجراء التصحيح')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('instructor_responded_at')
                            ->label('تاريخ وتوقيت رد المهندس')
                            ->dateTime('Y-m-d H:i')
                            ->default('لم يتم الرد بعد'),

                        TextEntry::make('instructor_response')
                            ->label('بيان المهندس / ما تم إنجازه')
                            ->default('بانتظار إنجاز المهندس للإجراء المطلوب')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('اعتماد المشرف وتاريخ القرارات')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('approver.name')
                            ->label('المشرف المعتمد')
                            ->default('—'),

                        TextEntry::make('approved_at')
                            ->label('تاريخ الاعتماد النهائي')
                            ->dateTime('Y-m-d H:i')
                            ->default('—'),

                        TextEntry::make('verification_notes')
                            ->label('ملاحظات التحقق الإشرافي')
                            ->default('—')
                            ->columnSpanFull(),

                        TextEntry::make('return_reason')
                            ->label('آخر سبب إعادة للتصحيح (إن وجد)')
                            ->default('لا توجد إعادات سابقة')
                            ->color('danger')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('سجل التدقيق والتتبع التاريخي الشامل (Audit History)')
                ->description('توثيق كافة الأحداث والقرارات الزمنية بدقة غير قابلة للحذف أو التجاوز.')
                ->schema([
                    RepeatableEntry::make('histories')
                        ->label('')
                        ->schema([
                            Grid::make(4)->schema([
                                TextEntry::make('actor.name')
                                    ->label('القائم بالإجراء')
                                    ->weight('bold'),

                                TextEntry::make('action')
                                    ->label('نوع الإجراء')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'created' => 'إنشاء الملاحظة',
                                        'submitted_for_review' => 'تسليم الرد للمراجعة',
                                        'returned_for_correction' => 'إعادة للتصحيح',
                                        'approved_completed' => 'اعتماد الإغلاق',
                                        default => $state,
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'created' => 'info',
                                        'submitted_for_review' => 'warning',
                                        'returned_for_correction' => 'danger',
                                        'approved_completed' => 'success',
                                        default => 'gray',
                                    }),

                                TextEntry::make('created_at')
                                    ->label('التوقيت')
                                    ->dateTime('Y-m-d H:i:s'),

                                TextEntry::make('comment')
                                    ->label('البيان / السبب')
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ]),
        ]);
    }
}
