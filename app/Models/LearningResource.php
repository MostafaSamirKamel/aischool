<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LearningResource extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'course_id',
        'group_id',
        'session_schedule_id',
        'session_number',
        'instructor_id',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'external_link',
        'visibility',
        'is_downloadable',
        'is_active',
        'download_count',
        'view_count',
    ];

    protected $casts = [
        'is_downloadable' => 'boolean',
        'is_active' => 'boolean',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'file_path' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($resource) {
            if (empty($resource->tenant_id)) {
                $resource->tenant_id = auth()->user()->tenant_id;
            }
            if (empty($resource->instructor_id)) {
                $resource->instructor_id = auth()->id();
            }
        });

        static::deleting(function ($resource) {
            // Delete file when resource is deleted
            if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
                Storage::disk('public')->delete($resource->file_path);
            }
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sessionSchedule(): BelongsTo
    {
        return $this->belongsTo(SessionSchedule::class);
    }

    // Helper Methods
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return $this->external_link;
    }

    public function getFileSizeFormatted(): string
    {
        if (!$this->file_size) return '-';
        
        $size = (int) $this->file_size;
        if ($size < 1024) {
            return $size . ' KB';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' MB';
        } else {
            return round($size / 1048576, 2) . ' GB';
        }
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where(function ($q) use ($courseId) {
            $q->where('course_id', $courseId)
              ->orWhere('visibility', 'all');
        });
    }

    public function scopeForGroup($query, $groupId)
    {
        return $query->where(function ($q) use ($groupId) {
            $q->where('group_id', $groupId)
              ->orWhere('visibility', 'all');
        });
    }
}
