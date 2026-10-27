<?php

namespace App\Filament\Instructor\Resources\GroupMessageResource\Pages;

use App\Filament\Instructor\Resources\GroupMessageResource\GroupMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroupMessages extends ListRecords
{
    protected static string $resource = GroupMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء رسالة جديدة'),
        ];
    }
}
