<?php

namespace App\Models\Concerns;

use App\Models\Scopes\BranchScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToBranch(): void
    {
        // تطبيق BranchScope تلقائيًا
        static::addGlobalScope(new BranchScope());

        // عند الإنشاء، تعيين tenant_id تلقائيًا
        static::creating(function ($model) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();
            $isSuper = $user->is_super_admin || $user->hasRole('super_admin') || $user->hasRole('owner');

            // إذا لم يكن سوبر أدمن، يتم إجبار tenant_id على tenant_id الخاص بالمستخدم لمنع التلاعب
            if (!$isSuper && !empty($user->tenant_id)) {
                $model->tenant_id = $user->tenant_id;
            } elseif (empty($model->tenant_id) && !empty($user->tenant_id)) {
                $model->tenant_id = $user->tenant_id;
            }
        });

        // حماية ضد تعديل tenant_id من قبل غير السوبر أدمن
        static::updating(function ($model) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();
            $isSuper = $user->is_super_admin || $user->hasRole('super_admin') || $user->hasRole('owner');

            if (!$isSuper && $model->isDirty('tenant_id')) {
                $model->tenant_id = $model->getOriginal('tenant_id');
            }
        });
    }

    /**
     * Get the branch (tenant) that owns the model
     * Table: tenants
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get the tenant (alias for branch)
     * Table: tenants
     */
    public function tenant(): BelongsTo
    {
        return $this->branch();
    }

    /**
     * Scope a query to only include models from a specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where($this->getTable() . '.tenant_id', $branchId);
    }

    /**
     * Disable branch scope (for Owner queries)
     */
    public function scopeWithoutBranchScope($query)
    {
        return $query->withoutGlobalScope(BranchScope::class);
    }

    /**
     * Check if model belongs to current user's branch
     */
    public function isOwnBranch(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        if (auth()->user()->isOwner()) {
            return true;
        }

        return $this->tenant_id === auth()->user()->tenant_id;
    }
}
