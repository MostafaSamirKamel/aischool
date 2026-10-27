<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGroupTransfer extends Model
{
    use HasFactory, BelongsToBranch;

    protected $table = 'student_group_transfers';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'enrollment_id',
        'from_group_id',
        'to_group_id',
        'transferred_by',
        'reason',
        'progress_warning_acknowledged',
        'metadata',
        'transferred_at',
    ];

    protected $casts = [
        'progress_warning_acknowledged' => 'boolean',
        'metadata' => 'array',
        'transferred_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function fromGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'from_group_id');
    }

    public function toGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'to_group_id');
    }

    public function transferredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
