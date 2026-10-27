<?php

namespace App\Filament\Admin\Resources\Revenues\Pages;

use App\Filament\Admin\Resources\Revenues\RevenueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRevenue extends EditRecord
{
    protected static string $resource = RevenueResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه حذف إيرادات
        if (!$user->is_super_admin) {
            $actions[] = DeleteAction::make();
            $actions[] = ForceDeleteAction::make();
            $actions[] = RestoreAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_revenue.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_revenue.title');
    }

}
