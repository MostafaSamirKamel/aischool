<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        try {
            // لا تطبق الفلترة إذا لم يكن هناك مستخدم مسجل دخول
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();
            if (!$user) {
                return;
            }

            // Super admin بدون tenant_id يرى جميع السجلات
            if (($user->is_super_admin || $user->hasRole('super_admin') || $user->hasRole('owner')) && is_null($user->tenant_id)) {
                return;
            }

            // جميع المستخدمين الآخرين يرون فقط سجلات tenant الخاص بهم
            if ($user->tenant_id) {
                $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
            } else {
                $builder->whereRaw('0 = 1');
            }
        } catch (\Exception $e) {
            // تسجيل الخطأ
            \Log::error('TenantScope error: ' . $e->getMessage());
        }
    }
}
