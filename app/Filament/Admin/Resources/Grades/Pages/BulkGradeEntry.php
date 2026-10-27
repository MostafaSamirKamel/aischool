<?php

namespace App\Filament\Admin\Resources\Grades\Pages;

use App\Models\Grade;
use App\Models\Exam;
use App\Models\Student;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class BulkGradeEntry extends Page
{
    protected static string $resource = \App\Filament\Admin\Resources\Grades\GradeResource::class;

    protected string $view = 'filament.admin.resources.grades.pages.bulk-grade-entry';

    protected static ?string $title = 'إدخال الدرجات الجماعي';

    protected static ?string $navigationLabel = 'إدخال جماعي';

    public ?array $data = [];
    public ?int $examId = null;
    public ?Collection $students = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('اختيار الامتحان')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('الامتحان')
                            ->options(function () {
                                $tenantId = auth()->user()->tenant_id;
                                return Exam::with('course', 'group')
                                    ->when(!auth()->user()->is_super_admin && $tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->get()
                                    ->mapWithKeys(fn($exam) => [
                                        $exam->id => $exam->title . ' - ' . $exam->course?->name . ' (' . $exam->group?->name . ')'
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->examId = $state;
                                $this->loadStudents();
                            }),

                        Forms\Components\Placeholder::make('exam_details')
                            ->label('تفاصيل الامتحان')
                            ->content(function () {
                                if (!$this->examId) {
                                    return 'اختر امتحان لعرض التفاصيل';
                                }
                                $exam = Exam::find($this->examId);
                                if (!$exam) {
                                    return 'امتحان غير موجود';
                                }
                                return "الدرجة الكلية: {$exam->total_marks} - درجة النجاح: {$exam->passing_marks}";
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function loadStudents(): void
    {
        if (!$this->examId) {
            $this->students = collect();
            return;
        }

        $exam = Exam::find($this->examId);
        if (!$exam) {
            $this->students = collect();
            return;
        }

        $this->students = Student::whereHas('enrollments', function ($query) use ($exam) {
            $query->where('group_id', $exam->group_id);
        })->get();
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        if (!isset($data['exam_id'])) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('يجب اختيار امتحان')
                ->send();
            return;
        }

        $exam = Exam::find($data['exam_id']);
        if (!$exam) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('الامتحان غير موجود')
                ->send();
            return;
        }

        $studentsData = request()->input('students', []);
        $savedCount = 0;

        foreach ($studentsData as $studentData) {
            if (isset($studentData['marks_obtained']) && is_numeric($studentData['marks_obtained'])) {
                $student = Student::find($studentData['student_id']);
                if (!$student) {
                    continue;
                }

                $enrollment = $student->enrollments()
                    ->where('group_id', $exam->group_id)
                    ->first();

                if (!$enrollment) {
                    continue;
                }

                $marksObtained = (float) $studentData['marks_obtained'];
                $percentage = ($marksObtained / $exam->total_marks) * 100;
                
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

                Grade::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'tenant_id' => $exam->tenant_id,
                        'enrollment_id' => $enrollment->id,
                        'marks_obtained' => $marksObtained,
                        'percentage' => round($percentage, 2),
                        'grade' => $grade,
                        'remarks' => $studentData['remarks'] ?? null,
                        'graded_by' => auth()->id(),
                        'graded_at' => now(),
                    ]
                );

                $savedCount++;
            }
        }

        Notification::make()
            ->success()
            ->title('تم الحفظ بنجاح')
            ->body("تم حفظ {$savedCount} درجة")
            ->send();

        $this->redirect(static::$resource::getUrl('index'));
    }
}
