<?php

namespace App\Filament\Instructor\Resources\GroupMessageResource\Pages;

use App\Filament\Instructor\Resources\GroupMessageResource\GroupMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGroupMessage extends EditRecord
{
    protected static string $resource = GroupMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // إذا تم تعديل الجدولة من فارغ إلى موعد محدد
        if (!empty($data['scheduled_at']) && $this->record->status === 'sent') {
            $data['status'] = 'scheduled';
            $data['sent_at'] = null;
        }
        
        // إذا تم إزالة الجدولة من رسالة مجدولة
        if (empty($data['scheduled_at']) && $this->record->status === 'scheduled') {
            $data['status'] = 'sent';
            $data['sent_at'] = now();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الرسالة بنجاح';
    }
}
