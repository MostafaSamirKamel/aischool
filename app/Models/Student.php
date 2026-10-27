<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([TenantScope::class])]
#[ObservedBy([StudentObserver::class])]
class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'student_code',
        'national_id',
        'birth_date',
        'gender',
        'phone',
        'phone_2',
        'address',
        'city',
        'governorate_id',
        'district_id',
        'detailed_address',
        'education_level',
        'school_name',
        'medical_notes',
        'status',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'birth_date' => 'date',
    ];

    protected $appends = ['age'];

    /**
     * Get formatted age (years, months, days)
     */
    public function getAgeAttribute(): ?string
    {
        if (!$this->birth_date) {
            return null;
        }

        $birthDate = \Carbon\Carbon::parse($this->birth_date);
        $now = now();
        $diff = $birthDate->diff($now);

        return sprintf(
            '%d سنة و %d شهر و %d يوم',
            $diff->y,
            $diff->m,
            $diff->d
        );
    }

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($student) {
            // التحقق من وجود تسجيلات في دورات
            if ($student->enrollments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الطالب. يوجد :count تسجيل في دورات مرتبط بهذا الطالب.',
                    ['count' => $student->enrollments()->count()]
                ));
            }

            // التحقق من وجود درجات
            if ($student->grades()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الطالب. يوجد :count درجة مسجلة لهذا الطالب.',
                    ['count' => $student->grades()->count()]
                ));
            }

            // التحقق من وجود سجلات حضور
            if ($student->attendanceRecords()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الطالب. يوجد :count سجل حضور لهذا الطالب.',
                    ['count' => $student->attendanceRecords()->count()]
                ));
            }

            // التحقق من وجود مدفوعات
            if ($student->payments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الطالب. يوجد :count مدفوعة مرتبطة بهذا الطالب.',
                    ['count' => $student->payments()->count()]
                ));
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function canceledEnrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)->where('status', 'cancelled');
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(EnrollmentCancellation::class);
    }

    /**
     * Get all groups this student is enrolled in through enrollments
     */
    public function groups(): HasManyThrough
    {
        return $this->hasManyThrough(
            Group::class,
            Enrollment::class,
            'student_id',
            'id',
            'id',
            'group_id'
        );
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function groupTransfers(): HasMany
    {
        return $this->hasMany(StudentGroupTransfer::class)->orderBy('transferred_at', 'desc');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Enrollment::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(StudentParent::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_parents', 'student_id', 'user_id')
            ->withPivot(['relation', 'is_primary'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }

    public function projects()
    {
        return $this->hasMany(StudentProject::class);
    }

    public function followUpTasks()
    {
        return $this->hasMany(StudentFollowUpTask::class);
    }

    /**
     * Get Unified Academic Timeline (Phase 3.2)
     */
    public function getAcademicTimeline(): array
    {
        $events = [];

        // 1. Enrollments
        foreach ($this->enrollments()->with(['courses', 'groups'])->get() as $enrollment) {
            $events[] = [
                'type' => 'enrollment',
                'title' => 'تسجيل دراسي جديد (' . ($enrollment->enrollment_code ?? 'قيد') . ')',
                'description' => 'التسجيل في دورة: ' . ($enrollment->courses->first()?->name ?? 'دورة') . ' - المجموعة: ' . ($enrollment->groups->first()?->name ?? 'عامة'),
                'date' => $enrollment->enrollment_date?->format('Y-m-d') ?? $enrollment->created_at?->format('Y-m-d'),
                'icon' => 'academic-cap',
                'color' => 'success',
            ];
        }

        // 2. Attendance records
        $attendanceRecords = AttendanceRecord::where('student_id', $this->id)
            ->with(['attendanceSession.course', 'attendanceSession.group'])
            ->latest('id')
            ->limit(20)
            ->get();

        foreach ($attendanceRecords as $att) {
            $statusText = match ($att->status) {
                'present' => 'حاضر',
                'absent' => 'غائب',
                'late' => 'متأخر',
                'excused' => 'معذور',
                default => $att->status,
            };
            $color = match ($att->status) {
                'present' => 'success',
                'absent' => 'danger',
                'late' => 'warning',
                default => 'gray',
            };
            $events[] = [
                'type' => 'attendance',
                'title' => "تسجيل حضور: {$statusText}",
                'description' => ($att->attendanceSession?->course?->name ?? 'جلسة') . ' (' . ($att->attendanceSession?->group?->name ?? '') . ')',
                'date' => $att->attendanceSession?->attendance_date?->format('Y-m-d') ?? $att->created_at?->format('Y-m-d'),
                'icon' => 'clock',
                'color' => $color,
            ];
        }

        // 3. Exam Grades
        $grades = Grade::where('student_id', $this->id)->with('exam.course')->latest('id')->limit(10)->get();
        foreach ($grades as $g) {
            $events[] = [
                'type' => 'exam',
                'title' => 'نتيجة اختبار: ' . ($g->exam?->title ?? 'امتحان'),
                'description' => "الدرجة: {$g->marks_obtained} من " . ($g->exam?->total_marks ?? 100),
                'date' => $g->created_at?->format('Y-m-d'),
                'icon' => 'document-check',
                'color' => 'info',
            ];
        }

        // 4. Assignment Submissions
        $submissions = AssignmentSubmission::where('student_id', $this->id)->with('assignment')->latest('id')->limit(10)->get();
        foreach ($submissions as $sub) {
            $events[] = [
                'type' => 'assignment',
                'title' => 'تسليم واجب: ' . ($sub->assignment?->title ?? 'واجب'),
                'description' => $sub->score !== null ? "تم التقييم: {$sub->score}" : 'قيد المراجعة',
                'date' => $sub->submitted_at?->format('Y-m-d') ?? $sub->created_at?->format('Y-m-d'),
                'icon' => 'clipboard-document-check',
                'color' => 'warning',
            ];
        }

        // 5. Follow-up Tasks
        foreach ($this->followUpTasks()->latest('id')->limit(5)->get() as $task) {
            $events[] = [
                'type' => 'follow_up',
                'title' => 'متابعة أكاديمية: ' . $task->title,
                'description' => "الحالة: {$task->status} - " . ($task->reason ?? ''),
                'date' => $task->created_at?->format('Y-m-d'),
                'icon' => 'exclamation-circle',
                'color' => $task->status === 'completed' ? 'success' : 'danger',
            ];
        }

        // Sort descending by date
        usort($events, function ($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });

        return $events;
    }

    /**
     * Check if student needs follow-up (Phase 3.3 & Phase 4)
     */
    public function getFollowUpStatusAttribute(): string
    {
        if ($this->followUpTasks()->whereIn('status', ['pending', 'in_progress'])->exists()) {
            return 'needs_follow_up';
        }

        // Check if absent 3 or more times recently
        $recentAbsences = AttendanceRecord::where('student_id', $this->id)
            ->where('status', 'absent')
            ->count();

        if ($recentAbsences >= 3) {
            return 'needs_follow_up';
        }

        return 'normal';
    }

    /**
     * Generate unique student code
     */
    public static function generateStudentCode(): string
    {
        $year = now()->year;
        $prefix = "STU-{$year}-";

        $lastStudent = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('student_code', 'like', $prefix . '%')
            ->orderBy('student_code', 'desc')
            ->first();

        $number = 1;
        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_code, -4);
            $number = $lastNumber + 1;
        }

        do {
            $code = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->withTrashed()
                ->where('student_code', $code)
                ->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }
}
