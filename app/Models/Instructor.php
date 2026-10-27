<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([TenantScope::class])]
class Instructor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'department_id',
        'employee_code',
        'national_id',
        'birth_date',
        'gender',
        'specialization',
        'bio',
        'qualifications',
        'years_of_experience',
        'status',
        'hired_date',
        'contract_type',
        'salary',
        'hourly_rate',
        'base_salary',
        'phone',
        'governorate',
        'emergency_contact',
    ];

    protected $casts = [
        'qualifications' => 'array',
        'hired_date' => 'date',
        'birth_date' => 'date',
        'salary' => 'decimal:2',
        'years_of_experience' => 'integer',
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
        static::deleting(function ($instructor) {
            // التحقق من وجود مجموعات مرتبطة
            if ($instructor->groups()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المدرس. يوجد :count مجموعة مرتبطة بهذا المدرس.',
                    ['count' => $instructor->groups()->count()]
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Explicitly assigned supervisors / secretaries for this instructor
     */
    public function assignedSupervisors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'supervision_instructor_assignments')
            ->whereNull('supervision_instructor_assignments.revoked_at')
            ->withPivot(['tenant_id', 'assigned_by', 'assigned_at', 'revoked_at'])
            ->withTimestamps();
    }

    /**
     * Supervisory notes on this instructor
     */
    public function supervisionNotes(): HasMany
    {
        return $this->hasMany(InstructorSupervisionNote::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
