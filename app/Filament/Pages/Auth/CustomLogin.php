<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Login as BaseLogin;
use SensitiveParameter;

class CustomLogin extends BaseLogin
{
    protected function getEmailFormComponent(): TextInput
    {
        return TextInput::make('email')
            ->label(__('auth.email_username_phone'))
            ->placeholder(__('auth.enter_email_username_phone'))
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * Allow login with email, phone, username, or common aliases.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim($data['email']);

        // Check by direct email, phone, or username
        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->orWhere('username', $login)
            ->first();

        // Fallback for common aliases if typed without @domain
        if (!$user) {
            $lower = strtolower($login);
            $aliasMap = [
                'superadmin' => 'superadmin@aischool.test',
                'admin' => 'admin@admin.com',
                'manager' => 'manager.cairo@aischool.test',
                'accountant' => 'accountant.cairo@aischool.test',
                'secretary' => 'secretary.cairo@aischool.test',
                'registration' => 'registration.cairo.1@aischool.test',
                'supervisor' => 'supervisor.cairo@aischool.test',
                'instructor' => 'instructor.cairo.1@aischool.test',
                'student' => 'student1@aischool.test',
                'student1' => 'student1@aischool.test',
                'parent' => 'parent1@aischool.test',
                'parent1' => 'parent1@aischool.test',
            ];

            if (isset($aliasMap[$lower])) {
                $user = User::where('email', $aliasMap[$lower])->first();
            }
        }


        return [
            'email' => $user ? $user->email : $login,
            'password' => $data['password'],
        ];
    }
}
