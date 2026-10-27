<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'governorate_id',
        'name_ar',
        'name_en',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($district) {
            // التحقق من وجود طلاب مرتبطين
            if ($district->students()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المنطقة. يوجد :count طالب مرتبط بهذه المنطقة.',
                    ['count' => $district->students()->count()]
                ));
            }
        });
    }

    /**
     * Get the governorate that owns this district
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get students in this district
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get name based on locale
     */
    public function getName(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }
}
