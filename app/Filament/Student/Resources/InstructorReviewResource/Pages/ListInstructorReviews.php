<?php

namespace App\Filament\Student\Resources\InstructorReviewResource\Pages;

use App\Filament\Student\Resources\InstructorReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstructorReviews extends ListRecords
{
    protected static string $resource = InstructorReviewResource::class;

    public function getTitle(): string
    {
        return 'تقييمات المدرسين';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة تقييم مدرس'),
        ];
    }
}
