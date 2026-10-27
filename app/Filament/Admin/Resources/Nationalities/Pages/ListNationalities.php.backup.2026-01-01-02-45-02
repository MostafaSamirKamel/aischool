<?php

namespace App\Filament\Admin\Resources\Nationalities\Pages;

use App\Filament\Admin\Resources\Nationalities\NationalityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNationalities extends ListRecords
{
    protected static string $resource = NationalityResource::class;

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
        return __('filament/admin/list_nationalities.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_nationalities.title');
    }

}
