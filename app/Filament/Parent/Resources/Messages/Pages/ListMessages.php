<?php

namespace App\Filament\Parent\Resources\Messages\Pages;

use App\Filament\Parent\Resources\Messages\MessagesResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMessages extends ListRecords
{
    protected static string $resource = MessagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string
    {
        return 'الرسائل والإشعارات';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }
}
