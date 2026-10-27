<?php

namespace App\Filament\Instructor\Resources\SupervisoryObservations\Pages;

use App\Filament\Instructor\Resources\SupervisoryObservations\InstructorSupervisoryObservationResource;
use Filament\Resources\Pages\ListRecords;

class ListInstructorSupervisoryObservations extends ListRecords
{
    protected static string $resource = InstructorSupervisoryObservationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
