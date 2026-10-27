<?php

namespace App\Filament\Admin\Resources\StudentFollowUpTaskResource\Pages;

use App\Filament\Admin\Resources\StudentFollowUpTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentFollowUpTasks extends ListRecords
{
    protected static string $resource = StudentFollowUpTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
