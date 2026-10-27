<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'capacity',
        'location',
        'description',
        'status',
        'equipment',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'status' => 'string',
        'equipment' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($classroom) {
            // التحقق من وجود مجموعات مرتبطة
            if ($classroom->groups()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الفصل. يوجد :count مجموعة مرتبطة بهذا الفصل.',
                    ['count' => $classroom->groups()->count()]
                ));
            }
        });
    }

    /**
     * Get all groups using this classroom
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get active groups only
     */
    public function activeGroups()
    {
        return $this->groups()->where('status', 'active');
    }

    /**
     * Check if classroom is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'active';
    }
}
