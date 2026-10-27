<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'image',
        'password',
        'phone',
        'date_of_birth',
        'address',
        'qualification',
        'job_title',
        'avatar',
        'company_email',
        'company_whatsapp',
        'company_website',
        'tenant_id',
        'is_super_admin',
        'is_tenant_owner',
        'status',
    ];

    /**
     * Route notifications for the SMS channel.
     */
    public function routeNotificationForSms($notification): ?string
    {
        return $this->phone;
    }

    /**
     * Route notifications for the WhatsApp channel.
     */
    public function routeNotificationForWhatsapp($notification): ?string
    {
        return $this->phone;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_super_admin' => 'boolean',
            'status' => 'string',
        ];
    }

    /**
     * Get the user's age based on date of birth
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return \Carbon\Carbon::parse($this->date_of_birth)->diffInYears(now());
    }

    /**
     * Get the tenant associated with the user
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branch associated with the user (alias for tenant)
     */
    public function branch()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get the instructor profile associated with the user
     */
    public function instructor()
    {
        return $this->hasOne(Instructor::class, 'user_id');
    }

    /**
     * Get the student profile associated with the user
     */
    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Get the supervisor profile associated with the user
     */
    public function supervisor()
    {
        return $this->hasOne(Supervisor::class, 'user_id');
    }

    /**
     * Get all students linked to this parent (for parent role)
     */
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'student_parents',
            'user_id',
            'student_id'
        )->withPivot([
            'relation',
            'is_primary',
            'can_pickup',
            'receive_notifications'
        ])->withTimestamps();
    }

    /**
     * Check if user is Owner/Super Admin
     */
    public function isOwner(): bool
    {
        return $this->is_super_admin || $this->hasRole('owner') || $this->hasRole('super_admin');
    }

    /**
     * Check if user is Branch Manager
     */
    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager') || $this->hasRole('manager');
    }

    /**
     * Check if user is Instructor/Teacher
     */
    public function isInstructor(): bool
    {
        return $this->hasRole('instructor') || $this->hasRole('teacher');
    }

    /**
     * Check if user is Secretary
     */
    public function isSecretary(): bool
    {
        return $this->hasRole('secretary');
    }

    /**
     * Check if user is Educational Supervisor or has supervision permissions
     */
    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor')
            || $this->hasRole('educational_supervisor')
            || $this->can('view_educational_supervision');
    }

    /**
     * Explicitly assigned groups for educational supervision
     */
    public function supervisedGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'supervision_group_assignments')
            ->whereNull('supervision_group_assignments.revoked_at')
            ->withPivot(['tenant_id', 'assigned_by', 'assigned_at', 'revoked_at'])
            ->withTimestamps();
    }

    /**
     * Explicitly assigned instructors for educational supervision
     */
    public function supervisedInstructors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'supervision_instructor_assignments')
            ->whereNull('supervision_instructor_assignments.revoked_at')
            ->withPivot(['tenant_id', 'assigned_by', 'assigned_at', 'revoked_at'])
            ->withTimestamps();
    }

    public function supervisionGroupAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupervisionGroupAssignment::class);
    }

    public function supervisionInstructorAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupervisionInstructorAssignment::class);
    }

    /**
     * Check if user is Student
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is Parent
     */
    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    /**
     * Get the default panel for the user based on their role
     */
    public function getDefaultPanel(): string
    {
        if ($this->isOwner()) {
            return 'owner';
        }

        if ($this->isStudent() || $this->isParent()) {
            return 'student'; // سيتم إنشاءه لاحقًا
        }

        return 'branch';
    }

    /**
     * Get branch_id (alias for tenant_id)
     */
    public function getBranchIdAttribute(): ?int
    {
        return $this->tenant_id;
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Super Admin has full unrestricted access
        if ($this->is_super_admin) {
            return true;
        }

        // Allow active users to authenticate on any login gate;
        // RedirectIfWrongPanel middleware automatically routes each user to their designated panel:
        // (admin, instructor, supervisor, student, parent) without 403 blocks.
        return true;
    }

}
