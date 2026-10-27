<?php

namespace App\Filament\Instructor\Resources\Exams\Pages;

use App\Filament\Instructor\Resources\Exams\ExamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_exams.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_exams.title');
    }

}
