<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // إذا كنا في console أو اختبارات ولم يكن هناك مستخدم مسجل، لا نطبق الفلترة
        if (app()->runningInConsole() || app()->environment('testing')) {
            if (!auth()->check()) {
                return;
            }
        }

        // إذا لم يكن هناك مستخدم مسجل دخول، نرجع نتيجة فارغة (حماية)
        if (!auth()->check()) {
            $builder->whereRaw('0 = 1');
            return;
        }

        $user = auth()->user();

        // Super Admin (is_super_admin = true OR role = super_admin / owner) بدون tenant_id يشوف كل البيانات
        if (($user->is_super_admin || $user->hasRole('super_admin') || $user->hasRole('owner')) && is_null($user->tenant_id)) {
            return; // لا نطبق أي فلترة
        }

        // إذا كان المستخدم لا يملك tenant_id وليس super admin، لا يرى أي بيانات
        if (is_null($user->tenant_id)) {
            $builder->whereRaw('0 = 1');
            return;
        }

        // المستخدمين العاديين يشوفوا بيانات tenant بتاعهم فقط
        $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
    }
}
