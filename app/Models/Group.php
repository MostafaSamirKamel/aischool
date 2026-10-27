<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Observers\GroupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([GroupObserver::class])]
class Group extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'course_id',
        'classroom_id',
        'instructor_id',
        'supervisor_id',
        'name',
        'capacity',
        'max_students',
        'schedule',
        'start_date',
        'end_date',
        'status',
        'completed_sessions',
        'total_sessions',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'max_students' => 'integer',
        'schedule' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set tenant_id on creation
        static::creating(function ($model) {
            if (empty($model->tenant_id) && auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Additional model events
     */
    protected static function booted()
    {
        static::deleting(function ($group) {
            // التحقق من وجود تسجيلات مرتبطة
            if ($group->enrollments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المجموعة. يوجد :count تسجيل طلاب مرتبط بهذه المجموعة.',
                    ['count' => $group->enrollments()->count()]
                ));
            }

            // التحقق من وجود جداول حصص
            if ($group->sessionSchedules()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المجموعة. يوجد :count جدول حصص مرتبط بهذه المجموعة.',
                    ['count' => $group->sessionSchedules()->count()]
                ));
            }
        });
    }

    /**
     * Get the course this group belongs to
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the classroom assigned to this group
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the instructor teaching this group
     */
    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    /**
     * Get the supervisor assigned to this group (legacy field)
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Explicitly assigned supervisors / secretaries for this group
     */
    public function assignedSupervisors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'supervision_group_assignments')
            ->whereNull('supervision_group_assignments.revoked_at')
            ->withPivot(['tenant_id', 'assigned_by', 'assigned_at', 'revoked_at'])
            ->withTimestamps();
    }

    /**
     * Get enrollment courses for this group
     */
    public function enrollmentCourses()
    {
        return $this->hasMany(EnrollmentCourse::class);
    }

    /**
     * Get enrollments through enrollment courses
     */
    public function enrollments()
    {
        return $this->hasManyThrough(
            Enrollment::class,
            EnrollmentCourse::class,
            'group_id', // Foreign key on enrollment_courses table
            'id', // Foreign key on enrollments table
            'id', // Local key on groups table
            'enrollment_id' // Local key on enrollment_courses table
        );
    }

    /**
     * Get all students enrolled in this group through enrollments
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            Enrollment::class,
            'group_id',
            'id',
            'id',
            'student_id'
        );
    }

    /**
     * Get active students in this group (Eloquent way)
     * Returns query builder, not collection
     */
    public function activeStudents()
    {
        // Use whereHas with proper Eloquent relationships to avoid ambiguous columns
        return Student::where('students.status', 'active')
            ->whereHas('enrollments', function ($query) {
                $query->where('enrollments.status', 'active')
                    ->where(function ($sub) {
                        $sub->where('enrollments.group_id', $this->id)
                            ->orWhereHas('enrollmentCourses', function ($q) {
                                $q->where('enrollment_courses.group_id', $this->id)
                                    ->where('enrollment_courses.status', 'active');
                            });
                    });
            })
            ->distinct()
            ->select('students.*');
    }

    /**
     * Accessor for active students collection
     */
    public function getActiveStudentsAttribute()
    {
        return $this->activeStudents()->get();
    }

    /**
     * Get all session schedules for this group
     */
    public function sessionSchedules()
    {
        return $this->hasMany(SessionSchedule::class);
    }

    /**
     * Get all attendance sessions for this group
     */
    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Get active students count in this group
     */
    public function getActiveStudentsCount(): int
    {
        return $this->activeStudents()->count();
    }

    /**
     * Sync the capacity column with actual active students count
     */
    public function syncCapacity(): void
    {
        $this->update(['capacity' => $this->getActiveStudentsCount()]);
    }

    /**
     * Check if group has reached capacity
     */
    public function isFull(): bool
    {
        $max = $this->max_students ?? 0;
        if ($max <= 0) {
            return false;
        }
        $current = max((int) $this->capacity, $this->getActiveStudentsCount());
        return $current >= $max;
    }

    public function transfersIn()
    {
        return $this->hasMany(StudentGroupTransfer::class, 'to_group_id')->orderBy('transferred_at', 'desc');
    }

    public function transfersOut()
    {
        return $this->hasMany(StudentGroupTransfer::class, 'from_group_id')->orderBy('transferred_at', 'desc');
    }

    /**
     * Check if group is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get session progress as (completed/total)
     */
    public function getSessionProgressAttribute(): string
    {
        $completed = $this->completed_sessions ?? $this->attendanceSessions()->count();
        $total = $this->total_sessions ?? $this->sessionSchedules()->count();
        return "({$completed}/{$total})";
    }

    /**
     * Get real-time completed sessions count
     */
    public function getCompletedSessionsCountAttribute(): int
    {
        return (int) ($this->completed_sessions ?? $this->attendanceSessions()->count());
    }

    /**
     * Get real-time total sessions count
     */
    public function getTotalSessionsCountAttribute(): int
    {
        $total = $this->total_sessions ?? 0;
        if ($total === 0) {
            $total = $this->sessionSchedules()->count();
        }
        return (int) $total;
    }
}
