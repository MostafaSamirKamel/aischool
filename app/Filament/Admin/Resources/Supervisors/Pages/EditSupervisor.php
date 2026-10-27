<?php

namespace App\Filament\Admin\Resources\Supervisors\Pages;

use App\Filament\Admin\Resources\Supervisors\SupervisorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSupervisor extends EditRecord
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_supervisor.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_supervisor.title');
    }

}
