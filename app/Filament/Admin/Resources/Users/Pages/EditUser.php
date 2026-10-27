<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $user = $this->record;
        $currentUser = auth()->user();

        $actions = [];

        // منع حذف المستخدم نفسه
        if ($user->id === $currentUser->id) {
            return $actions;
        }

        // منع حذف الـ super admin الرئيسي ومالك الـ tenant
        if ($user->is_super_admin || $user->is_tenant_owner) {
            return $actions;
        }

        // التحقق من أن هذا المستخدم ليس آخر admin في الفرع
        if ($user->hasRole('admin')) {
            $adminCount = User::where('tenant_id', $user->tenant_id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                })
                ->count();

            // إذا كان آخر admin، لا يمكن حذفه
            if ($adminCount <= 1) {
                return $actions;
            }
        }

        $actions[] = DeleteAction::make();

        return $actions;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // التأكد من بقاء tenant_id من المستخدم الحالي إذا لم يكن super_admin
        if (!auth()->user()->is_super_admin) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }
        return $data;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_user.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_user.title');
    }

}
