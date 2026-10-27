<?php

namespace App\Filament\Supervisor\Resources\Groups\Pages;

use App\Filament\Supervisor\Resources\Groups\GroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/supervisor/list_groups.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/list_groups.title');
    }

}
