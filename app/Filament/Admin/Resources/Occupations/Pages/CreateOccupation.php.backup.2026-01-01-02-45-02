<?php

namespace App\Filament\Admin\Resources\Occupations\Pages;

use App\Filament\Admin\Resources\Occupations\OccupationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateOccupation extends CreateRecord
{
    protected static string $resource = OccupationResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_occupation.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(OccupationResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_occupation.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_occupation.title');
    }

}
