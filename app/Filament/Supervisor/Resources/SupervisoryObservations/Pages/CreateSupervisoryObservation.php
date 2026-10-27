<?php

namespace App\Filament\Supervisor\Resources\SupervisoryObservations\Pages;

use App\Filament\Supervisor\Resources\SupervisoryObservations\SupervisoryObservationResource;
use App\Models\SupervisoryObservation;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervisoryObservation extends CreateRecord
{
    protected static string $resource = SupervisoryObservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['supervisor_id'] = $user->id;
        $data['tenant_id'] = $user->tenant_id ?? 1;
        $data['status'] = SupervisoryObservation::STATUS_NEW;

        return $data;
    }

    protected function afterCreate(): void
    {
        $observation = $this->record;
        $user = auth()->user();

        // Create initial audit history entry
        $observation->histories()->create([
            'tenant_id' => $observation->tenant_id,
            'actor_id' => $user->id,
            'action' => 'created',
            'previous_status' => null,
            'new_status' => SupervisoryObservation::STATUS_NEW,
            'comment' => 'تم إنشاء الملاحظة الإشرافية وإسنادها للمهندس.',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
