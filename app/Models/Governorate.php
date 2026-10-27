<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($governorate) {
            // التحقق من وجود مناطق مرتبطة
            if ($governorate->districts()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المحافظة. يوجد :count منطقة مرتبطة بهذه المحافظة.',
                    ['count' => $governorate->districts()->count()]
                ));
            }

            // التحقق من وجود طلاب مرتبطين
            if ($governorate->students()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف المحافظة. يوجد :count طالب مرتبط بهذه المحافظة.',
                    ['count' => $governorate->students()->count()]
                ));
            }
        });
    }

    /**
     * Get districts for this governorate
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get students in this governorate
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

    /**
     * Get governorate by code
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
