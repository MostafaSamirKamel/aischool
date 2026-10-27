<?php

namespace App\Filament\Traits;

trait HasWidgetShield
{
    public static function canView(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        
        // Super admin can see everything
        if ($user && $user->is_super_admin) {
            return true;
        }

        // Check widget visibility based on panel context
        $panel = filament()->getCurrentPanel();
        
        if (!$panel) {
            return false;
        }

        $panelId = $panel->getId();

        // Map panel IDs to roles
        $panelRoles = [
            'admin' => ['admin', 'branch_manager', 'secretary'],
            'instructor' => ['instructor'],
            'supervisor' => ['supervisor'],
            'student' => ['student'],
            'parent' => ['parent'],
        ];

        // Check if user has appropriate role for current panel
        if (isset($panelRoles[$panelId])) {
            foreach ($panelRoles[$panelId] as $role) {
                if ($user && $user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false;
    }
}
