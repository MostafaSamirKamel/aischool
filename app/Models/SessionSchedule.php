<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class SessionSchedule extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'group_id',
        'instructor_id',
        'course_id',
        'classroom_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'duration_minutes',
        'title',
        'description',
        'type',
        'original_session_id',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'rescheduled_at',
        'rescheduled_by',
        'attendance_taken',
        'attendance_taken_at',
        'has_conflicts',
        'conflicts_data',
    ];

    protected $casts = [
        'date' => 'date',
        'cancelled_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'attendance_taken_at' => 'datetime',
        'attendance_taken' => 'boolean',
        'has_conflicts' => 'boolean',
        'conflicts_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($schedule) {
            // Auto-calculate duration
            if ($schedule->start_time && $schedule->end_time) {
                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
                $schedule->duration_minutes = abs((int) $end->diffInMinutes($start));
            }

            // Check for conflicts before saving
            $schedule->checkConflicts();
        });

        static::deleting(function ($schedule) {
            // حذف جميع سجلات الحضور المرتبطة (Cascade Delete)
            $schedule->attendanceRecords()->delete();
        });

        // عند تغيير حالة الجلسة إلى مكتملة → تحقق هل يجب إنهاء الكورسات
        static::updated(function ($schedule) {
            if ($schedule->isDirty('status') && $schedule->status === 'completed' && $schedule->group_id) {
                static::autoCompleteEnrollmentCourses($schedule->group_id);
            }
        });
    }

    /**
     * إذا اكتملت جميع الجلسات المجدولة لمجموعة → حوِّل كل EnrollmentCourse مرتبط إلى completed
     */
    protected static function autoCompleteEnrollmentCourses(int $groupId): void
    {
        $total = static::where('group_id', $groupId)->count();
        $completed = static::where('group_id', $groupId)->where('status', 'completed')->count();

        if ($total > 0 && $completed >= $total) {
            EnrollmentCourse::where('group_id', $groupId)
                ->where('status', 'active')
                ->update(['status' => 'completed']);
        }
    }

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }

    public function originalSession(): BelongsTo
    {
        return $this->belongsTo(SessionSchedule::class, 'original_session_id');
    }

    public function makeupSessions()
    {
        return $this->hasMany(SessionSchedule::class, 'original_session_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where(function ($q) {
                $q->where('date', '>', now()->toDateString())
                    ->orWhere(function ($q2) {
                        $q2->where('date', now()->toDateString())
                            ->where('start_time', '>=', now()->toTimeString());
                    });
            });
    }

    public function scopeToday($query)
    {
        return $query->where('date', now()->toDateString());
    }

    public function scopeForInstructor($query, int $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeForGroup($query, int $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeForClassroom($query, int $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Helper methods
    public function getTimeRangeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('H:i') . ' - ' . 
               Carbon::parse($this->end_time)->format('H:i');
    }

    public function formatDisplayName(): string
    {
        $date = $this->date ? Carbon::parse($this->date)->format('Y-m-d') : 'بدون تاريخ';
        $time = $this->time_range ?? '---';
        $groupName = $this->group?->name ?? 'بدون مجموعة';
        
        return "{$date} {$time} - {$groupName}";
    }

    public function getDurationFormattedAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} ساعة و {$minutes} دقيقة";
        } elseif ($hours > 0) {
            return "{$hours} ساعة";
        } else {
            return "{$minutes} دقيقة";
        }
    }

    public function isUpcoming(): bool
    {
        if ($this->status !== 'scheduled') return false;

        $sessionDateTime = Carbon::parse($this->date . ' ' . $this->start_time);
        return $sessionDateTime->isFuture();
    }

    public function isPast(): bool
    {
        $sessionDateTime = Carbon::parse($this->date . ' ' . $this->end_time);
        return $sessionDateTime->isPast();
    }

    public function isOngoing(): bool
    {
        if ($this->status !== 'scheduled') return false;

        $now = now();
        $start = Carbon::parse($this->date . ' ' . $this->start_time);
        $end = Carbon::parse($this->date . ' ' . $this->end_time);

        return $now->between($start, $end);
    }

    public function cancel(int $userId, string $reason): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
        ]);
    }

    /**
     * Reschedule this session by marking this as rescheduled and creating a linked makeup session
     */
    public function reschedule(string $newDate, string $newStartTime, string $newEndTime, ?int $userId = null, ?string $reason = null): SessionSchedule
    {
        $this->update([
            'status' => 'rescheduled',
            'cancellation_reason' => $reason ?? $this->cancellation_reason,
            'rescheduled_at' => now(),
            'rescheduled_by' => $userId,
        ]);

        return static::create([
            'tenant_id' => $this->tenant_id,
            'group_id' => $this->group_id,
            'instructor_id' => $this->instructor_id,
            'course_id' => $this->course_id,
            'classroom_id' => $this->classroom_id,
            'date' => $newDate,
            'start_time' => $newStartTime,
            'end_time' => $newEndTime,
            'title' => ($this->title ? $this->title . ' (تعويضية)' : 'جلسة تعويضية'),
            'description' => $this->description,
            'type' => 'makeup',
            'original_session_id' => $this->id,
            'status' => 'scheduled',
        ]);
    }

    public function markCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    public function markAttendanceTaken(): bool
    {
        return $this->update([
            'attendance_taken' => true,
            'attendance_taken_at' => now(),
        ]);
    }

    /**
     * Check for scheduling conflicts
     */
    public function checkConflicts(): void
    {
        $conflicts = [];

        // Check instructor conflicts
        $instructorConflicts = $this->checkInstructorConflicts();
        if ($instructorConflicts->isNotEmpty()) {
            $conflicts['instructor'] = $instructorConflicts->map(function ($conflict) {
                return [
                    'group' => $conflict->group->name,
                    'time' => $conflict->time_range,
                ];
            })->toArray();
        }

        // Check classroom conflicts
        if ($this->classroom_id) {
            $classroomConflicts = $this->checkClassroomConflicts();
            if ($classroomConflicts->isNotEmpty()) {
                $conflicts['classroom'] = $classroomConflicts->map(function ($conflict) {
                    return [
                        'group' => $conflict->group->name,
                        'instructor' => $conflict->instructor->user->name,
                        'time' => $conflict->time_range,
                    ];
                })->toArray();
            }
        }

        // Check student conflicts (students in multiple groups)
        $studentConflicts = $this->checkStudentConflicts();
        if (!empty($studentConflicts)) {
            $conflicts['students'] = $studentConflicts;
        }

        $this->has_conflicts = !empty($conflicts);
        $this->conflicts_data = !empty($conflicts) ? $conflicts : null;
    }

    /**
     * Check if instructor has another session at the same time
     */
    protected function checkInstructorConflicts(): Collection
    {
        return static::where('instructor_id', $this->instructor_id)
            ->where('tenant_id', $this->tenant_id)
            ->where('date', $this->date)
            ->where('status', 'scheduled')
            ->when($this->id, fn($q) => $q->where('id', '!=', $this->id))
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>=', $this->end_time);
                    });
            })
            ->with('group')
            ->get();
    }

    /**
     * Check if classroom is occupied at the same time
     */
    protected function checkClassroomConflicts(): Collection
    {
        return static::where('classroom_id', $this->classroom_id)
            ->where('tenant_id', $this->tenant_id)
            ->where('date', $this->date)
            ->where('status', 'scheduled')
            ->when($this->id, fn($q) => $q->where('id', '!=', $this->id))
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>=', $this->end_time);
                    });
            })
            ->with(['group', 'instructor.user'])
            ->get();
    }

    /**
     * Check if any students have conflicting sessions
     */
    protected function checkStudentConflicts(): array
    {
        if (!$this->group_id) return [];

        // Get active student IDs enrolled in this group via EnrollmentCourse
        $studentIds = EnrollmentCourse::where('group_id', $this->group_id)
            ->where('status', 'active')
            ->whereHas('enrollment', fn($q) => $q->where('status', 'active'))
            ->with('enrollment')
            ->get()
            ->pluck('enrollment.student_id')
            ->filter()
            ->unique();

        if ($studentIds->isEmpty()) return [];

        // Get other groups these students are actively enrolled in
        $otherGroupIds = EnrollmentCourse::whereIn('enrollment_id', function ($q) use ($studentIds) {
                $q->select('id')->from('enrollments')->whereIn('student_id', $studentIds)->where('status', 'active');
            })
            ->where('group_id', '!=', $this->group_id)
            ->where('status', 'active')
            ->pluck('group_id')
            ->unique();

        if ($otherGroupIds->isEmpty()) return [];

        // Find conflicting sessions
        $conflictingSessions = static::whereIn('group_id', $otherGroupIds)
            ->where('tenant_id', $this->tenant_id)
            ->where('date', $this->date)
            ->where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>=', $this->end_time);
                    });
            })
            ->with('group')
            ->get();

        if ($conflictingSessions->isEmpty()) return [];

        $conflicts = [];
        foreach ($conflictingSessions as $session) {
            $affectedCourses = EnrollmentCourse::where('group_id', $session->group_id)
                ->where('status', 'active')
                ->whereHas('enrollment', fn($q) => $q->whereIn('student_id', $studentIds)->where('status', 'active'))
                ->with('enrollment.student.user')
                ->get();

            foreach ($affectedCourses as $ec) {
                $studentName = $ec->enrollment?->student?->user?->name ?? 'طالب';
                $conflicts[] = [
                    'student_name' => $studentName,
                    'conflicting_group' => $session->group?->name ?? 'مجموعة أخرى',
                    'time' => $session->time_range,
                ];
            }
        }

        return $conflicts;
    }
}
