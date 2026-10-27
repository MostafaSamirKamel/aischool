<?php

namespace App\Filament\Instructor\Resources\InstructorEvaluations\Pages;

use App\Filament\Instructor\Resources\InstructorEvaluations\InstructorMyEvaluationResource;
use Filament\Resources\Pages\ListRecords;

class ListInstructorMyEvaluations extends ListRecords
{
    protected static string $resource = InstructorMyEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
