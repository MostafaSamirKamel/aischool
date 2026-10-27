<?php

namespace App\Filament\Instructor\Resources\Assignments\Pages;

use App\Filament\Instructor\Resources\Assignments\AssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/instructor/edit_assignment.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/edit_assignment.title');
    }

}
