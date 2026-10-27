<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\EditRole as ShieldEditRole;
use Illuminate\Support\Arr;
use BezhanSalleh\FilamentShield\Support\Utils;

class EditRole extends ShieldEditRole
{
    protected static string $resource = RoleResource::class;
    
    public ?string $currentPanelType = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // تخزين panel_type الحالي
        $this->currentPanelType = $this->record->panel_type;
    }

    public function getTitle(): string
    {
        return 'تعديل الدور';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // تحميل الصلاحيات الحالية للدور
        $permissions = $this->record->permissions()->pluck('name')->toArray();

        // تجميع الصلاحيات حسب Model name (PascalCase)
        $groupedPermissions = [];

        // map من snake_case إلى PascalCase Model names
        // نبني هذا الـ map من الصلاحيات الموجودة
        foreach ($permissions as $permission) {
            // مثال: view_vault_transfer -> VaultTransfer
            // نحتاج لاستخراج اسم الموديل من الصلاحية

            // نزيل الأفعال المعروفة من بداية الصلاحية
            $actions = [
                'view_any_',
                'view_',
                'create_',
                'update_',
                'delete_',
                'delete_any_',
                'restore_',
                'restore_any_',
                'force_delete_',
                'force_delete_any_',
                'replicate_',
                'reorder_'
            ];

            $modelKey = $permission;
            foreach ($actions as $action) {
                if (str_starts_with($permission, $action)) {
                    $modelKey = substr($permission, strlen($action));
                    break;
                }
            }

            // تحويل من snake_case إلى PascalCase
            // vault_transfer -> VaultTransfer
            $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $modelKey)));

            if (!isset($groupedPermissions[$modelName])) {
                $groupedPermissions[$modelName] = [];
            }
            $groupedPermissions[$modelName][] = $permission;
        }
        
        // دمج الصلاحيات المجمعة مع البيانات
        return array_merge($data, $groupedPermissions);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
