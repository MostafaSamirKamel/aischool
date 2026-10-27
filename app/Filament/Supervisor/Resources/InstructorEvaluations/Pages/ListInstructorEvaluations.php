<?php

namespace App\Filament\Supervisor\Resources\InstructorEvaluations\Pages;

use App\Filament\Supervisor\Resources\InstructorEvaluations\InstructorEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstructorEvaluations extends ListRecords
{
    protected static string $resource = InstructorEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إجراء تقييم جديد')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
