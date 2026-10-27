<?php

namespace App\Filament\Admin\Resources\RevenueCategories\Pages;

use App\Filament\Admin\Resources\RevenueCategories\RevenueCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRevenueCategories extends ListRecords
{
    protected static string $resource = RevenueCategoryResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة فئات إيرادات
        if (!$user->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_revenue_categories.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_revenue_categories.title');
    }

}
