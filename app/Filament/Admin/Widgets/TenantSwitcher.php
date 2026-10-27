<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Redirects;

class TenantSwitcher extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.admin.widgets.tenant-switcher';

    #[Computed]
    public function tenants()
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // إذا كان الأدمن الرئيسي، أرجع جميع الفروع
        if ($user->is_super_admin || is_null($user->tenant_id)) {
            return Tenant::withoutGlobalScopes()->orderBy('name')->get();
        }

        // إرجاع الفرع الحالي للمستخدم العادي
        return Tenant::withoutGlobalScopes()->where('id', $user->tenant_id)->get();
    }

    public function switchTenant($tenantId)
    {
        $user = Auth::user();

        // السماح للأدمن الرئيسي بالتنقل بين أي فرع
        if ($user->is_super_admin || is_null($user->tenant_id)) {
            session(['tenant_id' => $tenantId]);
            $this->redirect('/admin');
            return;
        }

        // التحقق من أن المستخدم ينتمي إلى هذا الفرع
        if ($user->tenant_id != $tenantId) {
            $this->addError('tenant', 'غير مصرح للوصول إلى هذا الفرع');
            return;
        }

        session(['tenant_id' => $tenantId]);
        $this->redirect('/admin');
    }
}
