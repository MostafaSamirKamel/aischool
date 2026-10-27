<?php

namespace App\Filament\Admin\Resources\InstructorEvaluationAppeals;

use App\Filament\Admin\Resources\InstructorEvaluationAppeals\Pages\ListAdminInstructorEvaluationAppeals;
use App\Filament\Admin\Resources\InstructorEvaluationAppeals\Pages\ViewAdminInstructorEvaluationAppeal;
use App\Models\InstructorEvaluationAppeal;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminInstructorEvaluationAppealResource extends Resource
{
    protected static ?string $model = InstructorEvaluationAppeal::class;

    protected static ?string $slug = 'instructor-evaluation-appeals';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'اعتراضات تقييمات المهندسين';

    protected static ?string $modelLabel = 'اعتراض على تقييم';

    protected static ?string $pluralModelLabel = 'اعتراضات تقييمات المهندسين';

    protected static ?int $navigationSort = 16;

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير والمتابعة';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['evaluation.instructor.user', 'evaluation.supervisor', 'instructor.user', 'decidedByUser'])
            ->where('type', 'appeal');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('evaluation.evaluation_code')
                    ->label('كود التقييم')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('instructor.user.name')
                    ->label('المهندس المعترض')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('evaluation.supervisor.name')
                    ->label('المشرف التعليمي المقيّم')
                    ->searchable(),

                TextColumn::make('evaluation.evaluation_period')
                    ->label('الفترة')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('حالة الاعتراض')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'accepted' => 'مقبول ومعتمد للتعديل',
                        'rejected' => 'مرفوض بقرار الإدارة',
                        'returned_for_clarification' => 'معاد للاستيضاح',
                        'supervisor_responded' => 'بانتظار بت الإدارة (المشرف رد)',
                        default => 'جديد وبانتظار رد المشرف',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'returned_for_clarification' => 'warning',
                        'supervisor_responded' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('decided_at')
                    ->label('تاريخ قرار الإدارة')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'submitted' => 'جديد - بانتظار المشرف',
                        'supervisor_responded' => 'رد المشرف - بانتظار الإدارة',
                        'accepted' => 'مقبول',
                        'rejected' => 'مرفوض',
                        'returned_for_clarification' => 'معاد للاستيضاح',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('معاينة وبحث الاعتراض'),

                Action::make('decide_appeal')
                    ->label('إصدار قرار الإدارة النهائي')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->visible(fn(InstructorEvaluationAppeal $record) => in_array($record->status, ['submitted', 'supervisor_responded', 'returned_for_clarification']))
                    ->form([
                        Select::make('decision')
                            ->label('قرار الإدارة النهائي')
                            ->options([
                                'accepted' => 'قبول الاعتراض والتوجيه بتعديل التقييم',
                                'rejected' => 'رفض الاعتراض وتثبيت التقييم الأصلي',
                                'returned_for_clarification' => 'إعادة للمشرف لطلب استيضاح إضافي',
                            ])
                            ->required(),

                        Textarea::make('decision_reason')
                            ->label('أسباب ومسوغات قرار الإدارة (إلزامي للتوثيق)')
                            ->placeholder('أدخل أسباب القرار الإداري المستندة إلى السجلات والشواهد...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (InstructorEvaluationAppeal $record, array $data) {
                        $record->decideByManagement(
                            auth()->user(),
                            $data['decision'],
                            $data['decision_reason']
                        );

                        Notification::make()
                            ->title('تم إصدار قرار الإدارة النهائي بنجاح')
                            ->body("تم توثيق القرار وحفظه في سجل التدقيق التاريخي.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminInstructorEvaluationAppeals::route('/'),
            'view' => ViewAdminInstructorEvaluationAppeal::route('/{record}'),
        ];
    }
}
