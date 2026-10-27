<?php

namespace App\Filament\Supervisor\Resources\SupervisoryObservations\Pages;

use App\Filament\Supervisor\Resources\SupervisoryObservations\SupervisoryObservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupervisoryObservations extends ListRecords
{
    protected static string $resource = SupervisoryObservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إنشاء ملاحظة إشرافية جديدة')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
