<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'group_id',
        'session_schedule_id',
        'attendance_session_id',
        'session_number',
        'instructor_id',
        'title',
        'description',
        'attachment_path',
        'external_link',
        'due_date',
        'max_score',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => 'string',
        'session_number' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function sessionSchedule(): BelongsTo
    {
        return $this->belongsTo(SessionSchedule::class, 'session_schedule_id');
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function getSessionDateAttribute(): ?string
    {
        if ($this->sessionSchedule?->date) {
            return $this->sessionSchedule->date->format('Y-m-d');
        }
        if ($this->attendanceSession?->attendance_date) {
            return $this->attendanceSession->attendance_date->format('Y-m-d');
        }
        return null;
    }

    public function getSessionTitleAttribute(): ?string
    {
        return $this->sessionSchedule?->title 
            ?? $this->attendanceSession?->session_notes 
            ?? ($this->session_number ? "السيشن رقم {$this->session_number}" : null);
    }

    public function getCourseAttribute(): ?Course
    {
        return $this->group?->course;
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function getInstructorNameAttribute(): string
    {
        return $this->instructor?->user?->name
            ?? $this->group?->instructor?->user?->name
            ?? 'معلم المادة';
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function resubmissionGrants(): HasMany
    {
        return $this->hasMany(HomeworkResubmissionGrant::class);
    }

    public function deadlineExtensions(): HasMany
    {
        return $this->hasMany(HomeworkDeadlineExtension::class);
    }

    /**
     * Get the authoritative effective due date for a given student (or group-wide).
     */
    public function getEffectiveDueDateForStudent(?Student $student = null): ?\Carbon\Carbon
    {
        $baseDue = $this->due_date ? \Carbon\Carbon::parse($this->due_date)->endOfDay() : null;

        // Check for student-specific extension
        if ($student) {
            $studentExt = $this->deadlineExtensions()
                ->where('scope', 'student')
                ->where('student_id', $student->id)
                ->latest('new_due_date')
                ->first();

            if ($studentExt && $studentExt->new_due_date) {
                return \Carbon\Carbon::parse($studentExt->new_due_date);
            }
        }

        // Check for group-wide extension
        $groupExt = $this->deadlineExtensions()
            ->where('scope', 'group')
            ->latest('new_due_date')
            ->first();

        if ($groupExt && $groupExt->new_due_date) {
            return \Carbon\Carbon::parse($groupExt->new_due_date);
        }

        return $baseDue;
    }

    /**
     * Determine if a student can currently submit this homework
     */
    public function canStudentSubmit(Student $student): bool
    {
        $effectiveDue = $this->getEffectiveDueDateForStudent($student);
        $isBeforeDeadline = !$effectiveDue || now()->lte($effectiveDue);

        // Check if student has graded submission
        $hasGradedSubmission = $this->submissions()
            ->where('student_id', $student->id)
            ->where('graded', true)
            ->whereNotNull('score')
            ->exists();

        if ($hasGradedSubmission) {
            // Once graded, requires an active resubmission grant
            return $this->resubmissionGrants()
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->exists();
        }

        // If not graded, can submit if within deadline
        if ($isBeforeDeadline) {
            return true;
        }

        // If after deadline and not graded, can submit if has active resubmission grant
        return $this->resubmissionGrants()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Check if submission is closed for a specific student.
     */
    public function isSubmissionClosedForStudent(Student $student): bool
    {
        return !$this->canStudentSubmit($student);
    }
}
