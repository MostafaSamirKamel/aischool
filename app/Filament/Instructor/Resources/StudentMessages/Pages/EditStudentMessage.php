<?php

namespace App\Filament\Instructor\Resources\StudentMessages\Pages;

use App\Filament\Instructor\Resources\StudentMessages\StudentMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentMessage extends EditRecord
{
    protected static string $resource = StudentMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
