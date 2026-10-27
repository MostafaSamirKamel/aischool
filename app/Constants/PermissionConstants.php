<?php

namespace App\Constants;

class PermissionConstants
{
    // Permission Prefixes
    public const VIEW_ANY = 'ViewAny';
    public const VIEW = 'View';
    public const CREATE = 'Create';
    public const UPDATE = 'Update';
    public const DELETE = 'Delete';
    public const RESTORE = 'Restore';
    public const FORCE_DELETE = 'ForceDelete';

    // Default Roles
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_INSTRUCTOR = 'instructor';
    public const ROLE_STUDENT = 'student';
    public const ROLE_PARENT = 'parent';

    // Panel Types
    public const PANEL_ADMIN = 'admin';
    public const PANEL_SUPERVISOR = 'supervisor';
    public const PANEL_INSTRUCTOR = 'instructor';
    public const PANEL_STUDENT = 'student';
    public const PANEL_PARENT = 'parent';

    public static function getDefaultRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_SUPERVISOR,
            self::ROLE_INSTRUCTOR,
            self::ROLE_STUDENT,
            self::ROLE_PARENT,
        ];
    }

    public static function getPermissionActions(): array
    {
        return [
            self::VIEW_ANY,
            self::VIEW,
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::RESTORE,
            self::FORCE_DELETE,
        ];
    }
}
