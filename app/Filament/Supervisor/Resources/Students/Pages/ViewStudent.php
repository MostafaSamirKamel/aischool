<?php

namespace App\Filament\Supervisor\Resources\Students\Pages;

use App\Filament\Supervisor\Resources\Students\StudentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/supervisor/view_student.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/view_student.title');
    }

}
