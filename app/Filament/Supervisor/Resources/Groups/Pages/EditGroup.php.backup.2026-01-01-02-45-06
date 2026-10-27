<?php

namespace App\Filament\Supervisor\Resources\Groups\Pages;

use App\Filament\Supervisor\Resources\Groups\GroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/supervisor/edit_group.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/edit_group.title');
    }

}
