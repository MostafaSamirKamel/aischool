<?php

namespace App\Filament\Instructor\Resources\SupervisoryObservations;

use App\Filament\Instructor\Resources\SupervisoryObservations\Pages\ListInstructorSupervisoryObservations;
use App\Filament\Instructor\Resources\SupervisoryObservations\Pages\ViewInstructorSupervisoryObservation;
use App\Models\Instructor;
use App\Models\SupervisoryObservation;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstructorSupervisoryObservationResource extends Resource
{
    protected static ?string $model = SupervisoryObservation::class;

    protected static ?string $slug = 'supervisory-observations';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'توجيهاتي الإشرافية';

    protected static ?string $modelLabel = 'توجيه إشرافي';

    protected static ?string $pluralModelLabel = 'توجيهاتي الإشرافية';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'المتابعة الإشرافية';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $instructor = Instructor::where('user_id', $user->id)->first();
        $instructorId = $instructor?->id ?? 0;

        return parent::getEloquentQuery()
            ->where('instructor_id', $instructorId)
            ->with(['supervisor', 'group.course']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('observation_code')
                    ->label('كود الملاحظة')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('category_label')
                    ->label('التصنيف')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('title')
                    ->label('الموضوع')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->default('—'),

                TextColumn::make('priority')
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

                TextColumn::make('status')
                    ->label('الحالة')
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

                TextColumn::make('expected_resolution_date')
                    ->label('المهلة المحددة')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        SupervisoryObservation::STATUS_NEW => 'جديدة تتطلب إجراء',
                        SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
                        SupervisoryObservation::STATUS_COMPLETED => 'مكتملة',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('معاينة التفاصيل'),

                Action::make('submit_correction')
                    ->label('إرسال الإجراء للمراجعة')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn(SupervisoryObservation $record) => $record->status === SupervisoryObservation::STATUS_NEW)
                    ->form([
                        Textarea::make('response')
                            ->label('بيان الإجراء التصحيحي المنفذ (إلزامي)')
                            ->placeholder('وضّح للمشرف ما تم تنفيذه لمعالجة الملاحظة...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SupervisoryObservation $record, array $data) {
                        $record->submitInstructorResponse(auth()->user(), $data['response']);

                        Notification::make()
                            ->title('تم إرسال الإجراء لمراجعة المشرف')
                            ->body("أصبحت حالة الملاحظة {$record->observation_code} بانتظار مراجعة واعتماد المشرف.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorSupervisoryObservations::route('/'),
            'view' => ViewInstructorSupervisoryObservation::route('/{record}'),
        ];
    }
}
