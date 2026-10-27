<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToBranch;

class Supervisor extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_number',
        'national_id',
        'date_of_birth',
        'gender',
        'nationality',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'city',
        'hire_date',
        'employment_type',
        'status',
        'education_level',
        'specialization',
        'certifications',
        'years_of_experience',
        'base_salary',
        'allowances',
        'payment_type',
        'work_days',
        'work_start_time',
        'work_end_time',
        'permissions',
        'can_view_attendance',
        'can_manage_resources',
        'can_view_grades',
        'can_send_messages',
        'profile_photo',
        'resume_file',
        'id_copy',
        'other_documents',
        'bio',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'years_of_experience' => 'integer',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'work_days' => 'array',
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
        'permissions' => 'array',
        'can_view_attendance' => 'boolean',
        'can_manage_resources' => 'boolean',
        'can_view_grades' => 'boolean',
        'can_send_messages' => 'boolean',
        'other_documents' => 'array',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nationalityRelation(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
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

    public function scopeFullTime($query)
    {
        return $query->where('employment_type', 'full_time');
    }

    public function scopePartTime($query)
    {
        return $query->where('employment_type', 'part_time');
    }

    // Helper Methods
    public function getTotalSalary(): float
    {
        return $this->base_salary + $this->allowances;
    }

    public function getActiveGroupsCount(): int
    {
        return $this->groups()->where('status', 'active')->count();
    }

    public function getTotalStudentsCount(): int
    {
        return $this->groups()
            ->where('status', 'active')
            ->withCount('enrollments')
            ->get()
            ->sum('enrollments_count');
    }

    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => 'inactive']);
    }

    public function suspend(): bool
    {
        return $this->update(['status' => 'suspended']);
    }

    public function terminate(): bool
    {
        return $this->update(['status' => 'terminated']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canManageGroup(Group $group): bool
    {
        return $this->groups()->where('id', $group->id)->exists();
    }
}
