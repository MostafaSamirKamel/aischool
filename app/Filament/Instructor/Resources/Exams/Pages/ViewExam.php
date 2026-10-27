<?php

namespace App\Filament\Instructor\Resources\Exams\Pages;

use App\Filament\Instructor\Resources\Exams\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
