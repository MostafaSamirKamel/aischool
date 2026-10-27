<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentParent extends Model
{
    use HasFactory, BelongsToBranch;

    protected $table = 'student_parents';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'user_id',
        'relation',
        'is_primary',
        'can_pickup',
        'receive_notifications',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'can_pickup' => 'boolean',
        'receive_notifications' => 'boolean',
        'can_receive_notifications' => 'boolean',
    ];

    // Relations
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
