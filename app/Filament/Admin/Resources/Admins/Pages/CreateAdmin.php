<?php

namespace App\Filament\Admin\Resources\Admins\Pages;

use App\Filament\Admin\Resources\Admins\AdminResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check if user account creation is requested
        if (isset($data['create_user_account']) && $data['create_user_account'] === true) {
            // Create user account
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['user_password']),
                'phone' => $data['phone'] ?? null,
                'tenant_id' => auth()->user()->tenant_id,
            ]);

            // Assign secretary role (or admin role if you have one)
            $user->assignRole('secretary');

            // Set user_id
            $data['user_id'] = $user->id;
        }

        // Set tenant_id
        $data['tenant_id'] = auth()->user()->tenant_id;

        // Remove temporary fields
        unset($data['create_user_account'], $data['user_name'], $data['user_email'], $data['user_password']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
