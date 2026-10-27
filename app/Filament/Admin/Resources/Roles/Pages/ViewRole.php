<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Filament\Admin\Resources\Roles\Schemas\RoleInfolist;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole as ShieldViewRole;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ViewRole extends ShieldViewRole
{
    protected static string $resource = RoleResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        $record = parent::resolveRecord($key);

        // تحميل الصلاحيات مع الدور
        $record->load('permissions');

        return $record;
    }

    public function infolist(Schema $schema): Schema
    {
        return RoleInfolist::make($schema);
    }
}
