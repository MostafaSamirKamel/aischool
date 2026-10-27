<?php

namespace App\Filament\Admin\Resources\SmsConfigs\Pages;

use App\Filament\Admin\Resources\SmsConfigs\SmsConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSmsConfig extends EditRecord
{
    protected static string $resource = SmsConfigResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_sms_config.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(SmsConfigResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
            return [];
        }

        return [
            Actions\Action::make('test_sms')
                ->label(__('filament/admin/edit_sms_config.test_sms'))
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('اختبار إرسال رسالة')
                ->modalDescription('سيتم إرسال رسالة تجريبية للتأكد من عمل الإعدادات')
                ->action(function () {
                    Notification::make()
                        ->title(__('filament/admin/edit_sms_config.تمإرسالرسالةتجريبية'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return __('filament/admin/edit_sms_config.title') . __('filament/admin/sms_settings.title');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament/admin/sms_settings.saved');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_sms_config.title');
    }

}
