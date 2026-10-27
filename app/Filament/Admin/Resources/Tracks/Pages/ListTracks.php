<?php

namespace App\Filament\Admin\Resources\Tracks\Pages;

use App\Filament\Admin\Resources\Tracks\TrackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTracks extends ListRecords
{
    protected static string $resource = TrackResource::class;

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
        return __('filament/admin/list_tracks.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_tracks.title');
    }

}
