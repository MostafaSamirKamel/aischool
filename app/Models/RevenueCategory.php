<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueCategory extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'name_en',
        'description',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($category) {
            // التحقق من وجود إيرادات مرتبطة
            if ($category->revenues()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف فئة الإيرادات. يوجد :count إيراد مرتبط بهذه الفئة.',
                    ['count' => $category->revenues()->count()]
                ));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function getTotalRevenuesAttribute(): float
    {
        return $this->revenues()->sum('amount');
    }
}
