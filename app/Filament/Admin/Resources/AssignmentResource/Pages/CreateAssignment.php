<?php

namespace App\Filament\Admin\Resources\AssignmentResource\Pages;

use App\Filament\Admin\Resources\AssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['instructor_id'] = auth()->user()->id;

        return $data;
    }
}
