<?php

namespace App\Filament\Admin\Resources\Revenues\Pages;

use App\Filament\Admin\Resources\Revenues\RevenueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRevenues extends ListRecords
{
    protected static string $resource = RevenueResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة إيرادات
        if (!$user->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_revenues.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_revenues.title');
    }

}
