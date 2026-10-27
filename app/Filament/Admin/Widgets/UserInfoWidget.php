<?php

namespace App\Filament\Admin\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class UserInfoWidget extends Widget
{
    use HasWidgetShield;

    protected ?string $heading = 'Current User';
    protected string $view = 'filament.admin.widgets.user-info';

    public static function canView(): bool
    {
        return auth()->user()?->is_super_admin === true;
    }

    public function getUserInfo(): array
    {
        $user = auth()->user();

        return [
            'name' => $user->name ?? 'N/A',
            'email' => $user->email ?? 'N/A',
            'role' => $user->getRoleNames()->first() ?? 'No Role',
            'is_super_admin' => $user->is_super_admin ?? false,
            'avatar_url' => $user->avatar ?? null,
            'tenant_name' => $user->tenant?->name ?? 'System',
        ];
    }
}
