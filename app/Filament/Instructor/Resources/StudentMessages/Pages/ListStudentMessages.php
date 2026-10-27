<?php

namespace App\Filament\Instructor\Resources\StudentMessages\Pages;

use App\Filament\Instructor\Resources\StudentMessages\StudentMessageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentMessages extends ListRecords
{
    protected static string $resource = StudentMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
