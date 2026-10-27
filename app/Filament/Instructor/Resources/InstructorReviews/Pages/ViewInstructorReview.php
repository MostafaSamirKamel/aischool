<?php

namespace App\Filament\Instructor\Resources\InstructorReviews\Pages;

use App\Filament\Instructor\Resources\InstructorReviews\InstructorReviewResource;
use Filament\Resources\Pages\ViewRecord;

class ViewInstructorReview extends ViewRecord
{
    protected static string $resource = InstructorReviewResource::class;

    public function getTitle(): string
    {
        return __('filament/instructor/view_instructor_review.title');
    }

    public function getView(): string
    {
        return 'filament.instructor.pages.view-instructor-review';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/view_instructor_review.title');
    }

}
