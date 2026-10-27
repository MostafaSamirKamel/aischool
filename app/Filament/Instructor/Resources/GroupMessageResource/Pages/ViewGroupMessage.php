<?php

namespace App\Filament\Instructor\Resources\GroupMessageResource\Pages;

use App\Filament\Instructor\Resources\GroupMessageResource\GroupMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupMessage extends ViewRecord
{
    protected static string $resource = GroupMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->subject;
    }
}
