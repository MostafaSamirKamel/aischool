<?php

namespace App\Filament\Admin\Resources\RevenueCategories\Pages;

use App\Filament\Admin\Resources\RevenueCategories\RevenueCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRevenueCategory extends EditRecord
{
    protected static string $resource = RevenueCategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure tenant_id is preserved during updates
        if (!isset($data['tenant_id']) && auth()->check() && auth()->user()->tenant_id) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه حذف فئات الإيرادات
        if (!$user->is_super_admin) {
            $actions[] = DeleteAction::make();
            $actions[] = ForceDeleteAction::make();
            $actions[] = RestoreAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_revenue_category.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_revenue_category.title');
    }

}
