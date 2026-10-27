<?php

namespace App\Filament\Instructor\Resources\AssignmentSubmissions\Pages;

use App\Filament\Instructor\Resources\AssignmentSubmissions\AssignmentSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssignmentSubmissions extends ListRecords
{
    protected static string $resource = AssignmentSubmissionResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         CreateAction::make(),
    //     ];
    // }
    public function getTitle(): string
    {
        return __('filament/instructor/list_assignment_submissions.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/list_assignment_submissions.title');
    }

}
