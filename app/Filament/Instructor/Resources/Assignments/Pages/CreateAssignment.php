<?php

namespace App\Filament\Instructor\Resources\Assignments\Pages;

use App\Filament\Instructor\Resources\Assignments\AssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $instructor = auth()->user()->instructor;
        $data['instructor_id'] = $instructor->id;
        $data['tenant_id'] = auth()->user()->tenant_id;
        
        return $data;
    }
}
