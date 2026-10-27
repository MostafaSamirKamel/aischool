<?php

namespace App\Filament\Instructor\Resources\Exams\RelationManagers;

use Filament\Tables;
use App\Models\Student;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class GradesRelationManager extends RelationManager
{
    protected static string $relationship = 'grades';

    protected static ?string $title = 'الدرجات';

    protected static ?string $modelLabel = 'درجة';

    public function form(Schema $schema): Schema
    {
        $exam = $this->getOwnerRecord();
        
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('الطالب')
                    ->options(function () use ($exam) {
                        // جلب الطلاب المسجلين في المجموعة
                        if (!$exam->group_id) {
                            return [];
                        }
                        return Student::whereHas('enrollments', function ($query) use ($exam) {
                            $query->where('group_id', $exam->group_id);
                        })->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) use ($exam) {
                        if ($state) {
                            $enrollment = \App\Models\Enrollment::where('student_id', $state)
                                ->where('group_id', $exam->group_id)
                                ->first();
                            if ($enrollment) {
                                $set('enrollment_id', $enrollment->id);
                            }
                        }
                    }),

                Hidden::make('enrollment_id'),

                TextInput::make('marks_obtained')
                    ->label('الدرجة المحصلة')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(fn() => $exam->total_marks)
                    ->suffix("من {$exam->total_marks}")
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) use ($exam) {
                        if ($state !== null && $exam->total_marks > 0) {
                            $percentage = round(($state / $exam->total_marks) * 100, 2);
                            $set('percentage', $percentage);
                            
                            // حساب التقدير
                            $grade = match(true) {
                                $percentage >= 90 => 'A+',
                                $percentage >= 85 => 'A',
                                $percentage >= 80 => 'B+',
                                $percentage >= 75 => 'B',
                                $percentage >= 70 => 'C+',
                                $percentage >= 65 => 'C',
                                $percentage >= 60 => 'D+',
                                $percentage >= 50 => 'D',
                                default => 'F'
                            };
                            $set('grade', $grade);
                        }
                    }),

                TextInput::make('percentage')
                    ->label('النسبة المئوية')
                    ->disabled()
                    ->suffix('%')
                    ->dehydrated(),

                TextInput::make('grade')
                    ->label('التقدير')
                    ->disabled()
                    ->dehydrated(),

                Textarea::make('remarks')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('student.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marks_obtained')
                    ->label('الدرجة')
                    ->sortable()
                    ->formatStateUsing(fn($record) => "{$record->marks_obtained} / {$record->exam->total_marks}"),

                TextColumn::make('percentage')
                    ->label('النسبة المئوية')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'info',
                        $state >= 60 => 'warning',
                        default => 'danger'
                    }),

                TextColumn::make('grade')
                    ->label('التقدير')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'A+', 'A' => 'success',
                        'B+', 'B' => 'info',
                        'C+', 'C' => 'warning',
                        default => 'danger'
                    }),

                TextColumn::make('remarks')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('graded_at')
                    ->label('تاريخ التصحيح')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade')
                    ->label('التقدير')
                    ->options([
                        'A+' => 'A+',
                        'A' => 'A',
                        'B+' => 'B+',
                        'B' => 'B',
                        'C+' => 'C+',
                        'C' => 'C',
                        'D+' => 'D+',
                        'D' => 'D',
                        'F' => 'F',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة درجة')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['tenant_id'] = auth()->user()->tenant_id;
                        $data['graded_by'] = auth()->id();
                        $data['graded_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
