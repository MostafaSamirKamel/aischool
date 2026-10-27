<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
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
            // التحقق من وجود مصروفات مرتبطة
            if ($category->expenses()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف فئة المصروفات. يوجد :count مصروف مرتبط بهذه الفئة.',
                    ['count' => $category->expenses()->count()]
                ));
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }
}
