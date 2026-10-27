<?php

namespace App\Filament\Admin\Resources\Instructors\Pages;

use App\Filament\Admin\Resources\Instructors\InstructorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateInstructor extends CreateRecord
{
    protected static string $resource = InstructorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create user account first
        $user = User::create([
            'name' => $data['user_name'],
            'username' => $data['user_username'] ?? null,
            'email' => $data['user_email'],
            'password' => Hash::make($data['user_password']),
            'phone' => $data['user_phone'] ?? null,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        // Assign instructor role to the user
        $user->assignRole('instructor');

        // Replace user fields with user_id
        $data['user_id'] = $user->id;
        $data['tenant_id'] = auth()->user()->tenant_id;

        // Remove temporary fields
        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_phone']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
