<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFollowUpContact extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $table = 'student_follow_up_contacts';

    protected $fillable = [
        'tenant_id',
        'task_id',
        'student_id',
        'contacted_guardian_id',
        'guardian_name',
        'guardian_phone',
        'contact_method',
        'contact_result',
        'notes',
        'contacted_at',
        'recorded_by',
        'next_follow_up_date',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'next_follow_up_date' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($contact) {
            if ($contact->task_id) {
                $task = $contact->task;
                if ($task) {
                    if ($contact->contact_result === 'contacted' || $contact->contact_result === 'resolved') {
                        $task->markContacted($contact->notes);
                    } else {
                        // Unanswered or unreachable: strictly keep status as new
                        if (!$task->isContacted()) {
                            $task->update(['status' => StudentFollowUpTask::STATUS_NEW]);
                        }
                    }
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(StudentFollowUpTask::class, 'task_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function contactedGuardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contacted_guardian_id');
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->contact_method) {
            'phone_call' => 'اتصال هاتفي',
            'whatsapp' => 'واتساب',
            'in_person' => 'مقابلة شخصية',
            default => $this->contact_method,
        };
    }

    public function getResultLabelAttribute(): string
    {
        return match ($this->contact_result) {
            'contacted' => 'تم التواصل',
            'no_answer' => 'لم يرد',
            'unreachable' => 'الرقم غير متاح',
            'promised_to_submit' => 'وعد بالتسليم',
            'requires_follow_up' => 'يحتاج إعادة تواصل',
            'resolved' => 'تم التواصل والحل',
            default => $this->contact_result,
        };
    }

    public function getResultBadgeColorAttribute(): string
    {
        return match ($this->contact_result) {
            'contacted', 'resolved' => 'success',
            'promised_to_submit' => 'info',
            'no_answer', 'requires_follow_up' => 'warning',
            'unreachable' => 'danger',
            default => 'gray',
        };
    }
}
