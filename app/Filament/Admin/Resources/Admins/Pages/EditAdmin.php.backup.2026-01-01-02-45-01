<?php

namespace App\Filament\Admin\Resources\Admins\Pages;

use App\Filament\Admin\Resources\Admins\AdminResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه حذف إداريين
        if (!$user->is_super_admin) {
            $actions[] = ViewAction::make();
            $actions[] = DeleteAction::make();
            $actions[] = ForceDeleteAction::make();
            $actions[] = RestoreAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_admin.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_admin.title');
    }

}
