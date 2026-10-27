<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'course_id',
        'group_id',
        'attendance_session_id',
        'instructor_id',
        'title',
        'description',
        'type',
        'exam_date',
        'duration',
        'total_marks',
        'passing_marks',
        'status',
        'published_at',
        'instructions',
        'pdf_file',
        'images',
    ];

    protected $casts = [
        'exam_date' => 'datetime',
        'published_at' => 'datetime',
        'total_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2',
        'duration' => 'integer',
        'images' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($exam) {
            // منع الحذف إذا كان هناك محاولات مكتملة (درجات مسجلة)
            $completedAttempts = $exam->attempts()
                ->whereNotNull('completed_at')
                ->count();

            if ($completedAttempts > 0) {
                throw new \Exception(__(
                    'لا يمكن حذف الامتحان. يوجد :count محاولة مكتملة مرتبطة بهذا الامتحان.',
                    ['count' => $completedAttempts]
                ));
            }

            // حذف المحاولات غير المكتملة
            $exam->attempts()->each(function ($attempt) {
                $attempt->answers()->delete();
                $attempt->delete();
            });

            // حذف الدرجات
            $exam->grades()->delete();

            // Cascade-delete الأسئلة مع خياراتها وإجاباتها
            $exam->questions()->each(function ($question) {
                $question->answers()->delete();
                $question->options()->delete();
                $question->delete();
            });
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function retakeGrants(): HasMany
    {
        return $this->hasMany(ExamRetakeGrant::class);
    }

    public function hasStartedAttempts(): bool
    {
        return $this->attempts()->exists();
    }

    public function isResultsPublished(): bool
    {
        return $this->published_at !== null || $this->status === 'completed';
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

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('exam_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('exam_date', '<', now());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper Methods
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'مجدول',
            'in_progress' => 'جاري',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'quiz' => 'اختبار قصير',
            'midterm' => 'امتحان نصفي',
            'final' => 'امتحان نهائي',
            'assignment' => 'واجب',
            'project' => 'مشروع',
            default => $this->type,
        };
    }

    public function getPassingPercentageAttribute(): float
    {
        if ($this->total_marks == 0) return 0;
        return round(($this->passing_marks / $this->total_marks) * 100, 2);
    }

    public function getAverageMarksAttribute(): float
    {
        return $this->grades()->avg('marks_obtained') ?? 0;
    }

    public function getPassRateAttribute(): float
    {
        $totalGrades = $this->grades()->count();
        if ($totalGrades == 0) return 0;
        
        $passedGrades = $this->grades()
            ->where('marks_obtained', '>=', $this->passing_marks)
            ->count();
        
        return round(($passedGrades / $totalGrades) * 100, 2);
    }

    public function getGradesCountAttribute(): int
    {
        return $this->grades()->count();
    }

    public function getEndsAtAttribute(): ?Carbon
    {
        if (!$this->exam_date) {
            return null;
        }

        return $this->exam_date->copy()->addMinutes(max((int) $this->duration, 0));
    }

    public function isAvailableNow(): bool
    {
        if (!$this->exam_date || !in_array($this->status, ['scheduled', 'in_progress'], true)) {
            return false;
        }

        $now = now();
        $startsAt = $this->exam_date;
        $endsAt = $this->ends_at;

        if ($now->lt($startsAt)) {
            return false;
        }

        if ($endsAt && $now->gte($endsAt)) {
            return false;
        }

        return true;
    }

    public function hasEnded(): bool
    {
        if (!$this->exam_date) {
            return false;
        }

        $endsAt = $this->ends_at;

        return $endsAt ? now()->gte($endsAt) : false;
    }

    public function isPublished(): bool
    {
        return !empty($this->published_at);
    }
}
