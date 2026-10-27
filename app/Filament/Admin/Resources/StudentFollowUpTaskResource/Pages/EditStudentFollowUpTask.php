<?php

namespace App\Filament\Admin\Resources\StudentFollowUpTaskResource\Pages;

use App\Filament\Admin\Resources\StudentFollowUpTaskResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentFollowUpTask extends EditRecord
{
    protected static string $resource = StudentFollowUpTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
