<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions\Pages;

use App\Models\Group;
use App\Models\Enrollment;
use Filament\Actions\Action;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Instructor\Resources\AttendanceSessions\AttendanceSessionResource;

class TakeAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AttendanceSessionResource::class;

    protected static ?string $navigationLabel = 'تسجيل الحضور';

    protected static ?string $title = 'تسجيل الحضور اليومي';

    public ?array $data = [];

    public ?int $selectedGroupId = null;
    public ?string $selectedDate = null;
    public ?int $sessionId = null;
    public ?int $currentSessionNumber = null;
    public ?int $totalSessionsCount = null;
    public ?int $remainingSessionsCount = null;
    public $students = [];
    public $attendanceData = [];
    public $sessionNotes = '';

    public function mount(): void
    {
        // Get group_id and date from URL if provided
        $this->selectedGroupId = request()->query('group_id');
        $this->selectedDate = request()->query('date', now()->format('Y-m-d'));

        if ($this->selectedGroupId) {
            $this->loadStudents();
        }

        $this->form->fill([
            'group_id' => $this->selectedGroupId,
            'attendance_date' => $this->selectedDate,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('معلومات الجلسة')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('group_id')
                                    ->label('المجموعة')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        $user = auth()->user();

                                        if (!$user || !$user->instructor) {
                                            return [];
                                        }

                                        return Group::query()
                                            ->where('tenant_id', $user->tenant_id)
                                            ->whereHas('instructor', function ($query) use ($user) {
                                                $query->where('user_id', $user->id)
                                                    ->where('tenant_id', $user->tenant_id);
                                            })
                                            ->whereIn('status', ['active', 'scheduled'])
                                            ->with('course')
                                            ->get()
                                            ->mapWithKeys(function ($group) {
                                                return [
                                                    $group->id => $group->name . ' - ' . $group->course->name
                                                ];
                                            });
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedGroupId = $state;
                                        $this->loadStudents();
                                    }),
                                DatePicker::make('attendance_date')
                                    ->label('تاريخ الحضور')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now())
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedDate = $state;
                                        $this->loadStudents();
                                    }),
                            ]),
                        Textarea::make('session_notes')
                            ->label('ملاحظات الجلسة')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function loadStudents(): void
    {
        if (!$this->selectedGroupId) {
            $this->students = [];
            $this->attendanceData = [];
            $this->sessionId = null;
            $this->currentSessionNumber = null;
            $this->totalSessionsCount = null;
            $this->remainingSessionsCount = null;
            return;
        }

        // Check if session already exists
        $user = auth()->user();

        $authorizedGroup = Group::query()
            ->whereKey($this->selectedGroupId)
            ->where('tenant_id', $user?->tenant_id)
            ->whereHas('instructor', function ($query) use ($user) {
                $query->where('user_id', $user?->id)
                    ->where('tenant_id', $user?->tenant_id);
            })
            ->first();

        if (!$authorizedGroup) {
            $this->students = [];
            $this->attendanceData = [];
            $this->sessionId = null;
            $this->currentSessionNumber = null;
            $this->totalSessionsCount = null;
            $this->remainingSessionsCount = null;

            Notification::make()
                ->title('غير مسموح بالوصول لهذه المجموعة')
                ->danger()
                ->send();
            return;
        }

        $completedSessions = AttendanceSession::where('group_id', $authorizedGroup->id)->count();
        $totalSessions = $authorizedGroup->total_sessions_count;

        $this->totalSessionsCount = $totalSessions;
        $this->remainingSessionsCount = max($totalSessions - $completedSessions, 0);

        // Check if session already exists
        $session = AttendanceSession::where('group_id', $authorizedGroup->id)
            ->where('attendance_date', $this->selectedDate)
            ->first();

        if ($session) {
            $this->sessionId = $session->id;
            $this->sessionNotes = $session->session_notes;
            $this->currentSessionNumber = AttendanceSession::where('group_id', $authorizedGroup->id)
                ->whereDate('attendance_date', '<=', $this->selectedDate)
                ->count();

            // Load existing attendance records
            $existingRecords = AttendanceRecord::where('attendance_session_id', $session->id)
                ->with('student.user')
                ->get()
                ->keyBy('student_id');

            // Current active enrollments in the group
            $activeEnrollments = Enrollment::where('group_id', $authorizedGroup->id)
                ->where('status', 'active')
                ->with(['student.user'])
                ->get()
                ->keyBy('student_id');

            // Merge active students and historical students who attended this session
            $allStudentIds = $activeEnrollments->keys()->merge($existingRecords->keys())->unique();

            $this->students = $allStudentIds->map(function ($studentId) use ($activeEnrollments, $existingRecords) {
                $record = $existingRecords->get($studentId);
                $enrollment = $activeEnrollments->get($studentId);
                $student = $enrollment?->student ?? $record?->student;

                return [
                    'id' => $studentId,
                    'name' => $student?->user?->name ?? 'طالب',
                    'code' => $student?->student_code ?? '',
                    'status' => $record?->status ?? 'present',
                    'check_in_time' => $record?->check_in_time?->format('H:i'),
                    'notes' => $record?->notes,
                ];
            })->values()->toArray();
        } else {
            $this->sessionId = null;
            $this->sessionNotes = '';
            $this->currentSessionNumber = $completedSessions + 1;

            // Load students without existing attendance
            $this->students = Enrollment::where('group_id', $authorizedGroup->id)
                ->where('status', 'active')
                ->with(['student.user'])
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'id' => $enrollment->student_id,
                        'name' => $enrollment->student->user->name,
                        'code' => $enrollment->student->student_code,
                        'status' => 'present',
                        'check_in_time' => now()->format('H:i'),
                        'notes' => null,
                    ];
                })
                ->toArray();
        }

        // Initialize attendance data
        foreach ($this->students as $student) {
            $this->attendanceData[$student['id']] = [
                'status' => $student['status'],
                'check_in_time' => $student['check_in_time'],
                'notes' => $student['notes'],
            ];
        }
    }

    public function updateAttendanceStatus(int $studentId, string $status): void
    {
        $this->attendanceData[$studentId]['status'] = $status;

        // Auto-update check-in time for present/late students
        if (in_array($status, ['present', 'late']) && empty($this->attendanceData[$studentId]['check_in_time'])) {
            $this->attendanceData[$studentId]['check_in_time'] = now()->format('H:i');
        }
    }

    public function updateCheckInTime(int $studentId, ?string $time): void
    {
        $this->attendanceData[$studentId]['check_in_time'] = $time;
    }

    public function updateNotes(int $studentId, ?string $notes): void
    {
        $this->attendanceData[$studentId]['notes'] = $notes;
    }

    public function markAllPresent(): void
    {
        foreach ($this->students as $student) {
            $this->attendanceData[$student['id']]['status'] = 'present';
            if (empty($this->attendanceData[$student['id']]['check_in_time'])) {
                $this->attendanceData[$student['id']]['check_in_time'] = now()->format('H:i');
            }
        }

        Notification::make()
            ->title('تم تعليم جميع الطلاب كحاضرين')
            ->success()
            ->send();
    }

    public function markAllAbsent(): void
    {
        foreach ($this->students as $student) {
            $this->attendanceData[$student['id']]['status'] = 'absent';
            $this->attendanceData[$student['id']]['check_in_time'] = null;
        }

        Notification::make()
            ->title('تم تعليم جميع الطلاب كغائبين')
            ->warning()
            ->send();
    }

    public function saveAttendance(): void
    {
        try {
            // Validate
            if (!$this->selectedGroupId || !$this->selectedDate) {
                Notification::make()
                    ->title('يجب اختيار المجموعة والتاريخ')
                    ->danger()
                    ->send();
                return;
            }

            if (empty($this->students)) {
                Notification::make()
                    ->title('لا يوجد طلاب في هذه المجموعة')
                    ->warning()
                    ->send();
                return;
            }

            DB::beginTransaction();

            // Get group details
            $group = Group::query()
                ->whereKey($this->selectedGroupId)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->whereHas('instructor', function ($query) {
                    $query->where('user_id', auth()->id())
                        ->where('tenant_id', auth()->user()->tenant_id);
                })
                ->with('course', 'department')
                ->firstOrFail();

            // Create or update session
            $session = AttendanceSession::updateOrCreate(
                [
                    'group_id' => $this->selectedGroupId,
                    'attendance_date' => $this->selectedDate,
                ],
                [
                    'tenant_id' => auth()->user()->tenant_id,
                    'course_id' => $group->course_id,
                    'department_id' => $group->department_id,
                    'classroom' => $group->classroom?->name ?? $group->classroom?->code,
                    'session_notes' => $this->data['session_notes'] ?? null,
                    'recorded_by' => auth()->id(),
                ]
            );

            // Save attendance records
            foreach ($this->attendanceData as $studentId => $attendance) {
                AttendanceRecord::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $attendance['status'],
                        'check_in_time' => $attendance['check_in_time'] ?? null,
                        'check_out_time' => null,
                        'notes' => $attendance['notes'] ?? null,
                    ]
                );
            }

            // Update group completed sessions count
            $completedSessions = AttendanceSession::where('group_id', $this->selectedGroupId)->count();
            $group->update(['completed_sessions' => $completedSessions]);

            $this->totalSessionsCount = $group->total_sessions_count;
            $this->remainingSessionsCount = max($this->totalSessionsCount - $completedSessions, 0);
            $this->currentSessionNumber = min($completedSessions, $this->totalSessionsCount);

            DB::commit();

            Notification::make()
                ->title('تم حفظ الحضور بنجاح')
                ->body("تم تسجيل حضور {$session->records()->count()} طالب")
                ->success()
                ->send();

            // Reload data
            $this->loadStudents();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('خطأ في حفظ الحضور')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAllPresent')
                ->label('تعليم الكل حاضر')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action('markAllPresent'),
            Action::make('markAllAbsent')
                ->label('تعليم الكل غائب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action('markAllAbsent'),
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.instructor.pages.take-attendance');
    }
}
