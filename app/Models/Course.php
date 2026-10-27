<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'track_id',
        'name',
        'description',
        'duration_months',
        'sessions_count',
        'price',
        'prerequisites',
        'order',
        'status',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'sessions_count' => 'integer',
        'price' => 'decimal:2',
        'prerequisites' => 'array',
        'order' => 'integer',
        'status' => 'string',
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
        static::deleting(function ($course) {
            // التحقق من وجود مجموعات مرتبطة
            if ($course->groups()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الدورة. يوجد :count مجموعة مرتبطة بهذه الدورة.',
                    ['count' => $course->groups()->count()]
                ));
            }

            // التحقق من وجود جداول حصص
            if ($course->sessionSchedules()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الدورة. يوجد :count جدول حصص مرتبط بهذه الدورة.',
                    ['count' => $course->sessionSchedules()->count()]
                ));
            }
        });
    }

    /**
     * Get the track that owns this course
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get all groups for this course
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get all session schedules for this course
     */
    public function sessionSchedules()
    {
        return $this->hasMany(SessionSchedule::class);
    }

    /**
     * Get enrollments for this course
     */
    public function enrollments()
    {
        return $this->belongsToMany(Enrollment::class, 'enrollment_courses')
            ->withPivot('course_price')
            ->withTimestamps();
    }

    /**
     * Get active groups only
     */
    public function activeGroups()
    {
        return $this->groups()->where('status', 'active');
    }

    /**
     * Check if prerequisites are met
     */
    public function units()
    {
        return $this->hasMany(CourseUnit::class)->orderBy('order');
    }

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class)->orderBy('order');
    }

    public function projects()
    {
        return $this->hasMany(StudentProject::class);
    }
}
