<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole as ShieldCreateRole;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use BezhanSalleh\FilamentShield\Support\Utils;

class CreateRole extends ShieldCreateRole
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إضافة tenant_id إذا كان المستخدم admin وليس super admin
        if (auth()->user()->tenant_id && !auth()->user()->is_super_admin) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }

        $this->permissions = collect($data)
            ->filter(fn(mixed $permission, string $key): bool => !in_array($key, ['name', 'guard_name', 'select_all', 'panel_type', 'tenant_id', Utils::getTenantModelForeignKey()]))
            ->values()
            ->flatten()
            ->unique();

        // تصفية الصلاحيات لإزالة أي صلاحية لا يملكها المستخدم
        if (!auth()->user()->is_super_admin && !auth()->user()->hasRole('super_admin')) {
            $userPerms = auth()->user()->getAllPermissions()->pluck('name')->toArray();
            $this->permissions = $this->permissions->filter(fn ($p) => in_array($p, $userPerms, true));
        }

        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            return Arr::only($data, ['name', 'guard_name', 'panel_type', 'tenant_id', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'guard_name', 'panel_type', 'tenant_id']);
    }
}
