<?php

namespace App\Filament\Admin\Resources\AssignmentSubmissionResource\Pages;

use App\Filament\Admin\Resources\AssignmentSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentSubmission extends EditRecord
{
    protected static string $resource = AssignmentSubmissionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['score'] && !$data['graded_at']) {
            $data['graded_at'] = now();
            $data['status'] = 'graded';
        }

        return $data;
    }
}
