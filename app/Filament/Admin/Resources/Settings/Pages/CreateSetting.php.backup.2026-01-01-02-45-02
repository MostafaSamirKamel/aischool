<?php

namespace App\Filament\Admin\Resources\Settings\Pages;

use App\Filament\Admin\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function beforeCreate(): void
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // Check if a setting already exists for this tenant
        $existingSetting = Setting::where('tenant_id', $tenantId)->first();

        if ($existingSetting) {
            Notification::make()
                ->title(__('filament/admin/create_setting.يوجدسجلإعداداتبالفعل'))
                ->body('لا يمكن إضافة أكثر من سجل إعدادات واحد لكل فرع.')
                ->warning()
                ->send();

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_setting.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_setting.title');
    }

}
