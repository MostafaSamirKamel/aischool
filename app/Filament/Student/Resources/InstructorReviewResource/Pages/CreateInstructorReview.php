<?php

namespace App\Filament\Student\Resources\InstructorReviewResource\Pages;

use App\Filament\Student\Resources\InstructorReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInstructorReview extends CreateRecord
{
    protected static string $resource = InstructorReviewResource::class;

    public function getTitle(): string
    {
        return 'إضافة تقييم مدرس';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
