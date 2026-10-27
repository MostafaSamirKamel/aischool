<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class AttendanceSession extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'attendance_date',
        'course_id',
        'group_id',
        'department_id',
        'classroom',
        'session_notes',
        'recorded_by',
        'salary_payment_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->tenant_id)) {
                $session->tenant_id = auth()->check() ? auth()->user()->tenant_id : ($session->group?->tenant_id ?? null);
            }
            if (empty($session->recorded_by) && auth()->check()) {
                $session->recorded_by = auth()->id();
            }

            // Prevent duplicate session for same group and date before hitting DB unique key.
            if (!empty($session->group_id) && !empty($session->attendance_date)) {
                $date = \Carbon\Carbon::parse($session->attendance_date)->toDateString();

                $exists = static::withoutGlobalScopes()
                    ->where('group_id', $session->group_id)
                    ->whereDate('attendance_date', $date)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'attendance_date' => 'يوجد بالفعل جلسة حضور لهذه المجموعة في هذا التاريخ.',
                    ]);
                }
            }

            // Get classroom from group
            if ($session->group_id) {
                $group = Group::with('classroom')->find($session->group_id);
                if ($group && $group->classroom) {
                    $session->classroom = $group->classroom->name ?? $group->classroom->code;
                }
            }
        });

        // Auto-update instructor payment when session is created
        static::created(function ($session) {
            // Sync Group completed_sessions count
            if ($session->group_id) {
                $completedCount = static::where('group_id', $session->group_id)->count();
                Group::where('id', $session->group_id)
                    ->update(['completed_sessions' => $completedCount]);

                // إذا وصل عدد الجلسات المكتملة إلى الإجمالي → أكمل الكورسات تلقائياً
                $group = Group::find($session->group_id);
                if ($group) {
                    $totalSessions = $group->total_sessions > 0
                        ? $group->total_sessions
                        : $group->sessionSchedules()->count();

                    if ($totalSessions > 0 && $completedCount >= $totalSessions) {
                        EnrollmentCourse::where('group_id', $session->group_id)
                            ->where('status', 'active')
                            ->update(['status' => 'completed']);
                    }
                }
            }

            \App\Models\InstructorPayment::updateOrCreateForSession($session);
        });

        static::deleting(function ($session) {
            // حذف جميع سجلات الحضور المرتبطة (Cascade Delete)
            $session->records()->delete();
        });

        // Recalculate instructor payment after session is deleted
        static::deleted(function ($session) {
            // Sync Group completed_sessions count after deletion
            if ($session->group_id) {
                $completedCount = static::where('group_id', $session->group_id)->count();
                Group::where('id', $session->group_id)
                    ->update(['completed_sessions' => $completedCount]);
            }

            // Trigger recalculation by creating a temporary session object for the same period
            if ($session->group_id) {
                $group = Group::find($session->group_id);
                if ($group && $group->instructor_id) {
                    $instructor = \App\Models\Instructor::find($group->instructor_id);
                    if ($instructor) {
                        // Recalculate payment for the deleted session's month
                        $sessionDate = \Carbon\Carbon::parse($session->attendance_date);
                        $periodStart = $sessionDate->copy()->startOfMonth();
                        $periodEnd = $sessionDate->copy()->endOfMonth();

                        $payment = \App\Models\InstructorPayment::where('instructor_id', $instructor->id)
                            ->where('tenant_id', $session->tenant_id)
                            ->where('period_start', $periodStart)
                            ->where('period_end', $periodEnd)
                            ->first();

                        if ($payment) {
                            // Recalculate based on remaining unpaid sessions
                            $sessions = AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $instructor->id))
                                ->where('tenant_id', $session->tenant_id)
                                ->whereBetween('attendance_date', [$periodStart, $periodEnd])
                                ->whereNull('salary_payment_id')
                                ->with('group')
                                ->get();

                            $totalSessions = $sessions->count();
                            $ratePerSession = $instructor->hourly_rate ?? 100;
                            $baseAmount = $instructor->base_salary ?? 0;
                            $sessionsAmount = $totalSessions * $ratePerSession;

                            $sessionsData = $sessions->map(function ($sess) use ($ratePerSession) {
                                return [
                                    'id' => $sess->id,
                                    'date' => $sess->attendance_date->format('Y-m-d'),
                                    'group' => $sess->group->name ?? 'غير محدد',
                                    'rate' => $ratePerSession,
                                ];
                            })->toArray();

                            $payment->update([
                                'total_sessions' => $totalSessions,
                                'completed_sessions' => $totalSessions,
                                'sessions_amount' => $sessionsAmount,
                                'total_amount' => $sessionsAmount + $baseAmount + ($payment->bonus_amount ?? 0) - ($payment->deduction_amount ?? 0),
                                'sessions_data' => $sessionsData,
                            ]);
                        }
                    }
                }
            }
        });

        static::restored(function ($session) {
            // Restore deleted attendance records if soft-deleted
            $session->records()->restore();

            // Sync Group completed_sessions count after restoration
            if ($session->group_id) {
                $completedCount = static::where('group_id', $session->group_id)->count();
                Group::where('id', $session->group_id)
                    ->update(['completed_sessions' => $completedCount]);

                $group = Group::find($session->group_id);
                if ($group) {
                    $totalSessions = $group->total_sessions > 0
                        ? $group->total_sessions
                        : $group->sessionSchedules()->count();

                    if ($totalSessions > 0 && $completedCount >= $totalSessions) {
                        EnrollmentCourse::where('group_id', $session->group_id)
                            ->where('status', 'active')
                            ->update(['status' => 'completed']);
                    }
                }
            }

            \App\Models\InstructorPayment::updateOrCreateForSession($session);
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function instructor()
    {
        return $this->hasOneThrough(
            Instructor::class,
            Group::class,
            'id',
            'id',
            'group_id',
            'instructor_id'
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'attendance_session_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'attendance_session_id');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('attendance_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year);
    }

    // Helper Methods
    public function getTotalStudentsAttribute()
    {
        return $this->records()->count();
    }

    public function getPresentCountAttribute()
    {
        return $this->records()->where('status', 'present')->count();
    }

    public function getAbsentCountAttribute()
    {
        return $this->records()->where('status', 'absent')->count();
    }

    public function getLateCountAttribute()
    {
        return $this->records()->where('status', 'late')->count();
    }

    public function getAttendanceRateAttribute()
    {
        $total = $this->total_students;
        if ($total === 0) {
            return 0;
        }
        return round(($this->present_count / $total) * 100, 2);
    }
}
