<?php

namespace App\Filament\Instructor\Resources\StudentMessages\Pages;

use App\Filament\Instructor\Resources\StudentMessages\StudentMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentMessage extends CreateRecord
{
    protected static string $resource = StudentMessageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['sender_id'] = auth()->id();
        $data['sender_type'] = 'instructor';
        
        if ($data['status'] === 'sent') {
            $data['sent_at'] = now();
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
