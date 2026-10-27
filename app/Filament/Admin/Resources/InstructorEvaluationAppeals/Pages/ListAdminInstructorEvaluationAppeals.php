<?php

namespace App\Filament\Admin\Resources\InstructorEvaluationAppeals\Pages;

use App\Filament\Admin\Resources\InstructorEvaluationAppeals\AdminInstructorEvaluationAppealResource;
use Filament\Resources\Pages\ListRecords;

class ListAdminInstructorEvaluationAppeals extends ListRecords
{
    protected static string $resource = AdminInstructorEvaluationAppealResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
