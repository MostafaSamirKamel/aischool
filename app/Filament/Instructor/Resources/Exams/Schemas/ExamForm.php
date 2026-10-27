<?php

namespace App\Filament\Instructor\Resources\Exams\Schemas;

use App\Models\Group;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->description('البيانات الأساسية للامتحان')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('course_id')
                                    ->label(__('filament/instructor/exam_resource.course_id'))
                                    ->options(function () {
                                        $instructorId = auth()->user()->instructor?->id;
                                        if (!$instructorId) return [];

                                        return \App\Models\Course::whereHas('groups', function ($q) use ($instructorId) {
                                            $q->where('instructor_id', $instructorId)
                                              ->where('status', 'active');
                                        })
                                        ->where('status', 'active')
                                        ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('group_id', null)),

                                Select::make('group_id')
                                    ->label(__('filament/instructor/exam_resource.group_id'))
                                    ->options(function (Get $get) {
                                        $courseId = $get('course_id');
                                        if (!$courseId) {
                                            return [];
                                        }
                                        // المدرس يشوف بس مجموعاته
                                        return Group::where('course_id', $courseId)
                                            ->where('instructor_id', auth()->user()->instructor?->id)
                                            ->where('status', 'active')
                                            ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn(Get $get) => !$get('course_id')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label(__('filament/instructor/exam_resource.type'))
                                    ->options([
                                        'quiz' => __('filament/instructor/exam_resource.type.quiz'),
                                        'midterm' => __('filament/instructor/exam_resource.type.midterm'),
                                        'final' => __('filament/instructor/exam_resource.type.final'),
                                        'assignment' => __('filament/instructor/exam_resource.type.assignment'),
                                        'project' => __('filament/instructor/exam_resource.type.project')
                                    ])
                                    ->default('quiz')
                                    ->required(),
                            ]),

                        TextInput::make('title')
                            ->label(__('filament/instructor/exam_resource.title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label(__('filament/instructor/exam_resource.description'))
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('التفاصيل والتوقيت')
                    ->description('تفاصيل الامتحان والدرجات')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('exam_date')
                                    ->label(__('filament/instructor/exam_resource.exam_date'))
                                    ->required()
                                    ->seconds(false),

                                TextInput::make('duration')
                                    ->label(__('filament/instructor/exam_resource.duration'))
                                    ->required()
                                    ->numeric()
                                    ->default(60)
                                    ->suffix('دقيقة')
                                    ->minValue(1)
                                    ->maxValue(300),

                                Select::make('status')
                                    ->label(__('filament/instructor/exam_resource.status'))
                                    ->options([
                                        'scheduled' => __('filament/instructor/exam_resource.status.scheduled'),
                                        'in_progress' => __('filament/instructor/exam_resource.status.in_progress'),
                                        'completed' => __('filament/instructor/exam_resource.status.completed'),
                                        'cancelled' => __('filament/instructor/exam_resource.status.cancelled')
                                    ])
                                    ->default('scheduled')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_marks')
                                    ->label(__('filament/instructor/exam_resource.total_marks'))
                                    ->required()
                                    ->numeric()
                                    ->default(100)
                                    ->suffix('درجة')
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $passingMarks = $get('passing_marks');
                                        if ($passingMarks && $passingMarks > $state) {
                                            $set('passing_marks', round($state * 0.5));
                                        }
                                    }),

                                TextInput::make('passing_marks')
                                    ->label(__('filament/instructor/exam_resource.passing_marks'))
                                    ->required()
                                    ->numeric()
                                    ->suffix('درجة')
                                    ->minValue(1)
                                    ->rules([
                                        fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ($value > $get('total_marks')) {
                                                $fail('درجة النجاح يجب أن تكون أقل من أو تساوي الدرجة الكلية');
                                            }
                                        },
                                    ]),
                            ]),

                        RichEditor::make('instructions')
                            ->label(__('filament/instructor/exam_resource.instructions'))
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('أسئلة الامتحان (اختياري)')
                    ->description('يمكنك إضافة الأسئلة من الآن أو بعد الحفظ من تبويب الأسئلة')
                    ->visibleOn('create')
                    ->schema([
                        Repeater::make('initial_questions')
                            ->label('الأسئلة')
                            ->schema([
                                TextInput::make('question_text')
                                    ->label('نص السؤال')
                                    ->required()
                                    ->maxLength(65535)
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('points')
                                            ->label('الدرجة')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(0.25)
                                            ->step(0.25)
                                            ->suffix('درجة'),

                                        TextInput::make('order')
                                            ->label('الترتيب')
                                            ->numeric()
                                            ->minValue(1)
                                            ->placeholder('اختياري - يتم الترتيب تلقائياً عند الترك'),
                                    ]),

                                Repeater::make('options')
                                    ->label('الاختيارات')
                                    ->schema([
                                        TextInput::make('option_text')
                                            ->label('نص الاختيار')
                                            ->required()
                                            ->columnSpan(2),

                                        Select::make('is_correct')
                                            ->label('الإجابة الصحيحة')
                                            ->options([
                                                0 => 'لا',
                                                1 => 'نعم',
                                            ])
                                            ->default(0)
                                            ->required()
                                            ->native(false)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(4)
                                    ->minItems(3)
                                    ->maxItems(6)
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['option_text'] ?? null)
                                    ->addActionLabel('إضافة اختيار')
                                    ->columnSpanFull(),

                                FileUpload::make('pdf_file')
                                    ->label('PDF للسؤال (اختياري)')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->disk('local')
                                    ->directory('exams/questions/pdfs')
                                    ->visibility('private')
                                    ->downloadable()
                                    ->openable(),

                                FileUpload::make('images')
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
                                    ->reorderable(),
                            ])
                            ->collapsible()
                            ->reorderable()
                            ->addActionLabel('إضافة سؤال')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
