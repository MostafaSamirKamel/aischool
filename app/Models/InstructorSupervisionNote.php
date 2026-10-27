<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorSupervisionNote extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'instructor_id',
        'group_id',
        'attendance_session_id',
        'assignment_id',
        'exam_id',
        'category',
        'title',
        'content',
        'priority',
        'status',
        'created_by',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'attendance_delayed' => 'تأخر تسجيل الحضور',
            'homework_grading_delayed' => 'تأخر تصحيح الواجبات',
            'exam_marking_pending' => 'تأخر تقييم الامتحانات',
            'session_scheduling' => 'ملاحظة على مواعيد السيشنز',
            'positive_observation' => 'إشادة بأداء متميز',
            default => 'متابعة أكاديمية عامة',
        };
    }

    public function getCategoryBadgeColorAttribute(): string
    {
        return match ($this->category) {
            'attendance_delayed' => 'warning',
            'homework_grading_delayed' => 'danger',
            'exam_marking_pending' => 'danger',
            'session_scheduling' => 'info',
            'positive_observation' => 'success',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'منخفضة',
            'urgent' => 'عاجلة جداً',
            default => 'عادية',
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'urgent' => 'danger',
            default => 'warning',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'reviewed' => 'تمت المراجعة',
            'resolved' => 'تمت المعالجة',
            default => 'قيد المتابعة',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'reviewed' => 'info',
            'resolved' => 'success',
            default => 'warning',
        };
    }

    public function markResolved(User $resolver, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $resolver->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }
}
