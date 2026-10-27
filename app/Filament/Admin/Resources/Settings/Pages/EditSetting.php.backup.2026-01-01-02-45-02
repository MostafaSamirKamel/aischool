<?php

namespace App\Filament\Admin\Resources\Settings\Pages;

use App\Filament\Admin\Resources\Settings\SettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        // Hide delete action to prevent deletion of settings
        return [];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_setting.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_setting.title');
    }

}
