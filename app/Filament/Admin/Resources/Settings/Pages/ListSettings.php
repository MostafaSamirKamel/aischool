<?php

namespace App\Filament\Admin\Resources\Settings\Pages;

use App\Filament\Admin\Resources\Settings\SettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        // Hide create button if a setting already exists for this tenant
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $hasSettings = \App\Models\Setting::where('tenant_id', $tenantId)->exists();

        if ($hasSettings) {
            return [];
        }

        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_settings.title');
    }

}
