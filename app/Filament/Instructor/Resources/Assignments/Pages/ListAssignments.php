<?php

namespace App\Filament\Instructor\Resources\Assignments\Pages;

use App\Filament\Instructor\Resources\Assignments\AssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssignments extends ListRecords
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/instructor/list_assignments.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/list_assignments.title');
    }

}
