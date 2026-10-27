<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'order',
        'status',
        'settings',
    ];

    protected $casts = [
        'order' => 'integer',
        'status' => 'string',
        'settings' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set tenant_id on creation
        static::creating(function ($model) {
            if (empty($model->tenant_id) && auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Additional model events
     */
    protected static function booted()
    {
        static::deleting(function ($track) {
            // التحقق من وجود دورات مرتبطة
            if ($track->courses()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المسار. يوجد :count دورة مرتبطة بهذا المسار.',
                    ['count' => $track->courses()->count()]
                ));
            }
        });
    }

    /**
     * Get all courses for this track
     */
    public function courses()
    {
        return $this->hasMany(Course::class)->orderBy('order');
    }

    /**
     * Get active courses only
     */
    public function activeCourses()
    {
        return $this->courses()->where('status', 'active');
    }
}
