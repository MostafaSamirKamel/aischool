<?php

namespace App\Filament\Admin\Resources\Tracks\Pages;

use App\Filament\Admin\Resources\Tracks\TrackResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTrack extends CreateRecord
{
    protected static string $resource = TrackResource::class;

    public function mount(): void
    {
        parent::mount();

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/create_track.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه إنشاء سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(TrackResource::getUrl('index'));
        }
    }
    public function getTitle(): string
    {
        return __('filament/admin/create_track.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/create_track.title');
    }

}
