<?php

namespace App\Filament\Student\Resources\InstructorReviewResource\Pages;

use App\Filament\Student\Resources\InstructorReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstructorReview extends EditRecord
{
    protected static string $resource = InstructorReviewResource::class;

    public function getTitle(): string
    {
        return 'تعديل تقييم مدرس';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
