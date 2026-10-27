<?php

namespace App\Filament\Admin\Resources\Supervisors\Pages;

use App\Filament\Admin\Resources\Supervisors\SupervisorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupervisors extends ListRecords
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة مشرفين
        if (!($user->is_super_admin && is_null($user->tenant_id))) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_supervisors.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_supervisors.title');
    }

}
