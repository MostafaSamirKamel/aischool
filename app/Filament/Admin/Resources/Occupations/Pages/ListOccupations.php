<?php

namespace App\Filament\Admin\Resources\Occupations\Pages;

use App\Filament\Admin\Resources\Occupations\OccupationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOccupations extends ListRecords
{
    protected static string $resource = OccupationResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (!auth()->user()->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_occupations.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_occupations.title');
    }

}
