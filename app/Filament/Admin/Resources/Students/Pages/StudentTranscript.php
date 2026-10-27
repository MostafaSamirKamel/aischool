<?php

namespace App\Filament\Admin\Resources\Students\Pages;

use App\Models\Student;
use App\Models\Grade;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;

class StudentTranscript extends Page
{
    protected static string $resource = \App\Filament\Admin\Resources\Students\StudentResource::class;

    protected string $view = 'filament.admin.resources.students.pages.student-transcript';

    protected static ?string $title = 'كشف درجات الطالب';

    public ?array $data = [];
    public ?int $studentId = null;
    public ?Student $student = null;
    public $grades = [];
    public $stats = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('الطالب')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        $tenantId = auth()->user()->tenant_id;
                        return Student::where('status', 'active')
                            ->when(!auth()->user()->is_super_admin && $tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                            ->where(function ($q) use ($search) {
                                $q->where('student_code', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                            })
                            ->with('user')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn($s) => [$s->id => "{$s->student_code} - " . ($s->user?->name ?? 'طالب')]);
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $student = Student::with('user')->find($value);
                        return $student ? "{$student->student_code} - " . ($student->user?->name ?? 'طالب') : null;
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->studentId = $state;
                        $this->loadTranscript();
                    }),
            ])
            ->statePath('data');
    }

    protected function loadTranscript(): void
    {
        if (!$this->studentId) {
            $this->student = null;
            $this->grades = [];
            $this->stats = [];
            return;
        }

        $this->student = Student::with(['enrollments.groups.course'])->find($this->studentId);
        
        if (!$this->student) {
            $this->grades = [];
            $this->stats = [];
            return;
        }

        $this->grades = Grade::with(['exam.course', 'exam.group'])
            ->where('student_id', $this->studentId)
            ->orderBy('graded_at', 'desc')
            ->get()
            ->groupBy(function($grade) {
                return $grade->exam->course->name ?? 'غير محدد';
            });

        // Calculate statistics
        $allGrades = Grade::where('student_id', $this->studentId)->get();
        
        $this->stats = [
            'total_exams' => $allGrades->count(),
            'average_percentage' => round($allGrades->avg('percentage'), 2),
            'total_marks_obtained' => $allGrades->sum('marks_obtained'),
            'total_possible_marks' => $allGrades->sum(fn($grade) => $grade->exam->total_marks),
            'passing_count' => $allGrades->filter(fn($grade) => $grade->marks_obtained >= $grade->exam->passing_marks)->count(),
            'failing_count' => $allGrades->filter(fn($grade) => $grade->marks_obtained < $grade->exam->passing_marks)->count(),
            'highest_grade' => $allGrades->max('percentage'),
            'lowest_grade' => $allGrades->min('percentage'),
            'grade_distribution' => [
                'A+' => $allGrades->where('grade', 'A+')->count(),
                'A' => $allGrades->where('grade', 'A')->count(),
                'B+' => $allGrades->where('grade', 'B+')->count(),
                'B' => $allGrades->where('grade', 'B')->count(),
                'C+' => $allGrades->where('grade', 'C+')->count(),
                'C' => $allGrades->where('grade', 'C')->count(),
                'D' => $allGrades->where('grade', 'D')->count(),
                'F' => $allGrades->where('grade', 'F')->count(),
            ],
        ];
    }

    public function downloadPdf()
    {
        if (!$this->student) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('يجب اختيار طالب أولاً')
                ->send();
            return;
        }

        $pdfService = new \App\Services\TranscriptPdfService();
        return response()->streamDownload(function () use ($pdfService) {
            echo $pdfService->generate($this->student)->output();
        }, 'transcript_' . $this->student->id . '_' . now()->format('Y-m-d') . '.pdf');
    }
}
