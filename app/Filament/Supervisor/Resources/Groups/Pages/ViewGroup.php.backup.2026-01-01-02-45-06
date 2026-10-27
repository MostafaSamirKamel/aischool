<?php

namespace App\Filament\Supervisor\Resources\Groups\Pages;

use App\Filament\Supervisor\Resources\Groups\GroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroup extends ViewRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/supervisor/view_group.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/view_group.title');
    }

}
