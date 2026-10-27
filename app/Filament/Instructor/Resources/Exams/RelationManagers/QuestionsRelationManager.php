<?php

namespace App\Filament\Instructor\Resources\Exams\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ExamQuestion;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

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

                Forms\Components\FileUpload::make('pdf_file')
                    ->label('PDF للسؤال (اختياري)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->disk('local')
                    ->directory('exams/questions/pdfs')
                    ->visibility('private')
                    ->downloadable()
                    ->openable(),

                Forms\Components\FileUpload::make('images')
                    ->label('صور السؤال (اختياري)')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->maxSize(5120)
                    ->disk('local')
                    ->directory('exams/questions/images')
                    ->visibility('private')
                    ->downloadable()
                    ->openable()
                    ->previewable(true)
                    ->reorderable()
                    ->columnSpanFull(),

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
                            ->columnSpan(1)
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set, $livewire) {
                                if ($state) {
                                    // إذا تم تحديد هذا الاختيار كصحيح، قم بإلغاء تحديد الباقي
                                    $options = $get('options') ?? [];
                                    foreach ($options as $key => $option) {
                                        if ($key !== $livewire->mountedTableActionRecord && isset($option['is_correct']) && $option['is_correct']) {
                                            $options[$key]['is_correct'] = false;
                                        }
                                    }
                                }
                            }),

                        Forms\Components\Hidden::make('order')
                            ->default(fn($get, $state, $livewire) => 
                                count($livewire->data['options'] ?? [])
                            ),
                    ])
                    ->columns(3)
                    ->defaultItems(4)
                    ->minItems(3)
                    ->maxItems(6)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['option_text'] ?? null)
                    ->addActionLabel('إضافة اختيار')
                    ->columnSpanFull()
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $get): array {
                        $data['order'] = count($get('options'));
                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $get): array {
                        $options = $get('options') ?? [];
                        $optionsCount = count($options);

                        // التحقق من عدد الاختيارات (لا يقل عن 3)
                        if ($optionsCount < 3) {
                            \Filament\Notifications\Notification::make()
                                ->title('❌ خطأ في عدد الاختيارات')
                                ->body('يجب أن يحتوي السؤال على <strong>3</strong> اختيارات على الأقل. العدد الحالي: <strong>' . $optionsCount . '</strong>')
                                ->danger()
                                ->persistent()
                                ->send();

                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'options' => 'يجب أن يحتوي السؤال على 3 اختيارات على الأقل',
                            ]);
                        }

                        // التحقق من عدد الإجابات الصحيحة
                        $correctCount = 0;
                        foreach ($options as $option) {
                            if (isset($option['is_correct']) && $option['is_correct']) {
                                $correctCount++;
                            }
                        }

                        if ($correctCount > 1) {
                            \Filament\Notifications\Notification::make()
                                ->title('❌ خطأ')
                                ->body('لا يمكن اختيار أكثر من إجابة صحيحة واحدة لنفس السؤال')
                                ->danger()
                                ->persistent()
                                ->send();

                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'options' => 'لا يمكن اختيار أكثر من إجابة صحيحة واحدة',
                            ]);
                        }

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

                Tables\Columns\IconColumn::make('pdf_file')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn($record) => !empty($record->pdf_file))
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('images')
                    ->label('صور')
                    ->boolean()
                    ->getStateUsing(fn($record) => !empty($record->images) && count((array) $record->images) > 0)
                    ->alignCenter(),

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
                    ->createAnother(false),
            ])
            ->actions([
                EditAction::make()
                    ->label('تعديل'),
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
