<?php

namespace App\Filament\Admin\Resources\Grades\Schemas;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Enrollment;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Get;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // tenant_id is set automatically by BelongsToBranch trait

                Section::make('معلومات الامتحان والطالب')
                    ->description('اختر الامتحان والطالب')
                    ->schema([
                        Grid::make(['default' => 1])
                            ->schema([
                                Select::make('exam_id')
                                    ->label(__('filament/admin/grade_resource.exam_id'))
                                    ->relationship('exam', 'title', function ($query) {
                                        return $query->whereIn('status', ['scheduled', 'in_progress', 'completed']);
                                    })
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->title . ' (' . ($record->course?->name ?? 'عام') . ')')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('student_id', null);
                                        $set('enrollment_id', null);
                                    }),
                            ]),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                Select::make('student_id')
                                    ->label(__('filament/admin/grade_resource.student_id'))
                                    ->options(function (Get $get) {
                                        $examId = $get('exam_id');
                                        if (!$examId) {
                                            return [];
                                        }

                                        $exam = Exam::find($examId);
                                        if (!$exam) {
                                            return [];
                                        }

                                        return Student::whereHas('enrollments', function ($query) use ($exam) {
                                            $query->where('group_id', $exam->group_id);
                                        })
                                            ->with('user')
                                            ->where('status', 'active')
                                            ->get()
                                            ->mapWithKeys(fn($student) => [
                                                $student->id => ($student->user?->name ?? 'طالب #' . $student->id) . ' (' . ($student->user?->email ?? '-') . ')'
                                            ]);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabled(fn(Get $get) => !$get('exam_id'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, callable $set) {
                                        $examId = $get('exam_id');
                                        if (!$examId || !$state) {
                                            $set('enrollment_id', null);
                                            return;
                                        }

                                        $exam = Exam::find($examId);
                                        $enrollment = Enrollment::where('student_id', $state)
                                            ->where('group_id', $exam->group_id)
                                            ->first();

                                        $set('enrollment_id', $enrollment?->id);
                                    }),

                                Select::make('enrollment_id')
                                    ->label(__('filament/admin/grade_resource.enrollment_id'))
                                    ->relationship('enrollment', 'id')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])->columns(1),

                Section::make('الدرجات')
                    ->description('أدخل الدرجات المستحقة')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Placeholder::make('total_marks_display')
                                    ->label('الدرجة الكلية')
                                    ->content(function (Get $get) {
                                        $examId = $get('exam_id');
                                        if (!$examId) {
                                            return '-';
                                        }
                                        $exam = Exam::find($examId);
                                        return $exam ? $exam->total_marks . ' درجة' : '-';
                                    }),

                                TextInput::make('marks_obtained')
                                    ->label(__('filament/admin/grade_resource.marks_obtained'))
                                    ->required()
                                    ->numeric()
                                    ->suffix('درجة')
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Get $get, callable $set) {
                                        $examId = $get('exam_id');
                                        if (!$examId || !is_numeric($state)) {
                                            return;
                                        }

                                        $exam = Exam::find($examId);
                                        if (!$exam) {
                                            return;
                                        }

                                        // Calculate percentage
                                        $percentage = ($state / $exam->total_marks) * 100;
                                        $set('percentage', round($percentage, 2));

                                        // Calculate grade
                                        $grade = match (true) {
                                            $percentage >= 95 => 'A+',
                                            $percentage >= 90 => 'A',
                                            $percentage >= 85 => 'B+',
                                            $percentage >= 80 => 'B',
                                            $percentage >= 75 => 'C+',
                                            $percentage >= 70 => 'C',
                                            $percentage >= 60 => 'D',
                                            default => 'F',
                                        };
                                        $set('grade', $grade);
                                    })
                                    ->rules([
                                        fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $examId = $get('exam_id');
                                            if (!$examId) {
                                                return;
                                            }
                                            $exam = Exam::find($examId);
                                            if ($exam && $value > $exam->total_marks) {
                                                $fail('الدرجة المحصلة لا يمكن أن تكون أكبر من الدرجة الكلية (' . $exam->total_marks . ')');
                                            }
                                        },
                                    ]),

                                Placeholder::make('passing_marks_display')
                                    ->label('درجة النجاح')
                                    ->content(function (Get $get) {
                                        $examId = $get('exam_id');
                                        if (!$examId) {
                                            return '-';
                                        }
                                        $exam = Exam::find($examId);
                                        return $exam ? $exam->passing_marks . ' درجة' : '-';
                                    }),
                            ]),

                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextInput::make('percentage')
                                    ->label(__('filament/admin/grade_resource.percentage'))
                                    ->numeric()
                                    ->suffix('%')
                                    ->disabled()
                                    ->dehydrated(),

                                Select::make('grade')
                                    ->label(__('filament/admin/grade_resource.grade_label'))
                                    ->options(['A+' => __('filament/admin/grade_resource.grade.a+'), 'A' => __('filament/admin/grade_resource.grade.a'), 'B+' => __('filament/admin/grade_resource.grade.b+'), 'B' => __('filament/admin/grade_resource.grade.b'), 'C+' => __('filament/admin/grade_resource.grade.c+'), 'C' => __('filament/admin/grade_resource.grade.c'), 'D' => __('filament/admin/grade_resource.grade.d'), 'F' => __('filament/admin/grade_resource.grade.f')])
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        RichEditor::make('remarks')
                            ->label(__('filament/admin/grade_resource.remarks'))
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('معلومات التقييم')
                    ->description('معلومات المُقيِّم')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('graded_by_name')
                                    ->label(__('filament/admin/grade_resource.graded_by'))
                                    ->default(fn() => auth()->user()->name)
                                    ->disabled()
                                    ->dehydrated(false),

                                Hidden::make('graded_by')
                                    ->default(fn() => auth()->id()),

                                DateTimePicker::make('graded_at')
                                    ->label(__('filament/admin/grade_resource.graded_at'))
                                    ->default(now())
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])->columns(1)->collapsed(),
            ]);
    }
}
