<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إضافة tenant_id تلقائياً من المستخدم الحالي إذا لم يكن super_admin
        if (!auth()->user()->is_super_admin) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }
        return $data;
    }
}
