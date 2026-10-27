<?php

namespace App\Filament\Admin\Resources\Exams\RelationManagers;

use App\Models\ExamQuestion;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'أسئلة الامتحان';

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'الأسئلة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Textarea::make('question_text')
                    ->label('نص السؤال')
                    ->required()
                    ->columnSpanFull()
                    ->rows(3),

                Forms\Components\TextInput::make('points')
                    ->label('الدرجة')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(0.25)
                    ->step(0.25)
                    ->suffix('درجة'),

                Forms\Components\TextInput::make('order')
                    ->label('الترتيب')
                    ->required()
                    ->numeric()
                    ->default(fn() => ExamQuestion::where('exam_id', $this->ownerRecord->id)->max('order') + 1),

                Forms\Components\Repeater::make('options')
                    ->relationship('options')
                    ->label('الاختيارات')
                    ->schema([
                        Forms\Components\TextInput::make('option_text')
                            ->label('نص الاختيار')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('is_correct')
                            ->label('إجابة صحيحة')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Hidden::make('order')
                            ->default(fn($get, $state, $livewire) => 
                                count($livewire->data['options'] ?? [])
                            ),
                    ])
                    ->columns(3)
                    ->defaultItems(4)
                    ->minItems(2)
                    ->maxItems(6)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['option_text'] ?? null)
                    ->addActionLabel('إضافة اختيار')
                    ->columnSpanFull()
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $get): array {
                        $data['order'] = count($get('options'));
                        return $data;
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('السؤال')
                    ->limit(60)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('عدد الاختيارات')
                    ->counts('options')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('points')
                    ->label('الدرجة')
                    ->numeric(2)
                    ->suffix(' درجة')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_correct_option')
                    ->label('إجابة صحيحة')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->options()->where('is_correct', true)->exists())
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة سؤال')
                    ->icon('heroicon-o-plus')
                    ->modalWidth(\Filament\Support\Enums\Width::FourExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter(),
            ])
            ->actions([
                EditAction::make()
                    ->label('تعديل')
                    ->modalWidth(\Filament\Support\Enums\Width::FourExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter(),
                DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->emptyStateHeading('لا توجد أسئلة')
            ->emptyStateDescription('ابدأ بإضافة أسئلة الامتحان من الزر أعلاه')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }
}
