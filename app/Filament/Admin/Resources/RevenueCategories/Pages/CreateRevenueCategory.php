<?php

namespace App\Filament\Admin\Resources\RevenueCategories\Pages;

use App\Filament\Admin\Resources\RevenueCategories\RevenueCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRevenueCategory extends CreateRecord
{
    protected static string $resource = RevenueCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // tenant_id will be set automatically by BelongsToBranch trait
        // But we keep this as fallback for safety
        if (!isset($data['tenant_id']) && auth()->check() && auth()->user()->tenant_id) {
            $data['tenant_id'] = auth()->user()->tenant_id;
        }

        return $data;
    }
}
