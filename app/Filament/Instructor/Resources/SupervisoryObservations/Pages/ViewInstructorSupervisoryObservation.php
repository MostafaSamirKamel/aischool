<?php

namespace App\Filament\Instructor\Resources\SupervisoryObservations\Pages;

use App\Filament\Instructor\Resources\SupervisoryObservations\InstructorSupervisoryObservationResource;
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

class ViewInstructorSupervisoryObservation extends ViewRecord
{
    protected static string $resource = InstructorSupervisoryObservationResource::class;

    public function getTitle(): string
    {
        return "معاينة التوجيه الإشرافي: {$this->record->observation_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit_correction')
                ->label('إرسال الإجراء لمراجعة المشرف')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn() => $this->record->status === SupervisoryObservation::STATUS_NEW)
                ->form([
                    Textarea::make('response')
                        ->label('بيان الإجراء التصحيحي المنفذ (إلزامي)')
                        ->placeholder('وضّح للمشرف بالتفصيل ما تم إنجازه لمعالجة الملاحظة...')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->submitInstructorResponse(auth()->user(), $data['response']);

                    Notification::make()
                        ->title('تم إرسال الإجراء لمراجعة المشرف بنجاح')
                        ->body("أصبحت حالة الملاحظة {$this->record->observation_code} بانتظار مراجعة المشرف.")
                        ->success()
                        ->send();

                    $this->fillForm();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تفاصيل التوجيه الإشرافي')
                ->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('observation_code')
                            ->label('كود الملاحظة')
                            ->weight('bold'),

                        TextEntry::make('status')
                            ->label('الحالة الحالية')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
                                SupervisoryObservation::STATUS_COMPLETED => 'مكتملة ومعتمدة',
                                default => 'جديدة وتتطلب إجراء',
                            })
                            ->color(fn(string $state): string => match ($state) {
                                SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'warning',
                                SupervisoryObservation::STATUS_COMPLETED => 'success',
                                default => 'danger',
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

                    Grid::make(2)->schema([
                        TextEntry::make('supervisor.name')
                            ->label('المشرف التعليمي الموجه'),

                        TextEntry::make('expected_resolution_date')
                            ->label('المهلة المحددة للمعالجة')
                            ->date('Y-m-d')
                            ->weight('bold'),
                    ]),

                    TextEntry::make('title')
                        ->label('موضوع الملاحظة')
                        ->weight('bold'),

                    TextEntry::make('description')
                        ->label('شرح الملاحظة المرصودة'),

                    TextEntry::make('required_action')
                        ->label('الإجراء التصحيحي المطلوب منك تنفيذه')
                        ->color('primary')
                        ->weight('bold'),

                    TextEntry::make('return_reason')
                        ->label('سبب إعادة الملاحظة من المشرف (إن وجد)')
                        ->color('danger')
                        ->visible(fn() => !empty($this->record->return_reason)),
                ]),

            Section::make('بيانك وإجراء التصحيح المرسل')
                ->schema([
                    TextEntry::make('instructor_response')
                        ->label('رد المهندس')
                        ->default('لم يتم إرسال رد بعد، يمكنك الضغط على "إرسال الإجراء لمراجعة المشرف" أعلاه.')
                        ->columnSpanFull(),

                    TextEntry::make('instructor_responded_at')
                        ->label('تاريخ وتوقيت الإرسال')
                        ->dateTime('Y-m-d H:i')
                        ->default('—'),
                ]),

            Section::make('سجل المتابعة والتدقيق التاريخي')
                ->schema([
                    RepeatableEntry::make('histories')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('actor.name')
                                    ->label('القائم بالإجراء'),

                                TextEntry::make('action')
                                    ->label('نوع الإجراء')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'created' => 'إنشاء وتوجيه الملاحظة',
                                        'submitted_for_review' => 'تسليم الإجراء للمراجعة',
                                        'returned_for_correction' => 'إعادة للتصحيح',
                                        'approved_completed' => 'اعتماد الإغلاق النهائي',
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
                                    ->label('البيان')
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ]),
        ]);
    }
}
