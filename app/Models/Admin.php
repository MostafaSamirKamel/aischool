<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


#[ScopedBy([TenantScope::class])]
class Admin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_code',
        'national_id',
        'birth_date',
        'gender',
        'job_title',
        'department_id',
        'phone',
        'governorate',
        'emergency_contact',
        'hire_date',
        'salary',
        'status',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'salary' => 'decimal:2',
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

        $now = now();
        $diff = $this->birth_date->diff($now);

        return sprintf(
            '%d سنة و %d شهر و %d يوم',
            $diff->y,
            $diff->m,
            $diff->d
        );
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }


}
