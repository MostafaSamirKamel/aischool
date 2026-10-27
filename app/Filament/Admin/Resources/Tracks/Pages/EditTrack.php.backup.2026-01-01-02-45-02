<?php

namespace App\Filament\Admin\Resources\Tracks\Pages;

use App\Filament\Admin\Resources\Tracks\TrackResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTrack extends EditRecord
{
    protected static string $resource = TrackResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if (auth()->user()->is_super_admin) {
            Notification::make()
                ->title(__('filament/admin/edit_track.غيرمصرح'))
                ->body('الأدمن الرئيسي لا يمكنه تعديل سجلات في هذا القسم')
                ->danger()
                ->send();

            $this->redirect(TrackResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->is_super_admin) {
            return [];
        }

        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_track.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_track.title');
    }

}
