<?php

namespace App\Filament\Instructor\Resources\InstructorPayments\Pages;

use App\Filament\Instructor\Resources\InstructorPayments\InstructorPaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewInstructorPayment extends ViewRecord
{
    protected static string $resource = InstructorPaymentResource::class;

    public function getTitle(): string
    {
        return __('filament/instructor/view_instructor_payment.title');
    }

    public function getView(): string
    {
        return 'filament.instructor.pages.view-instructor-payment';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/view_instructor_payment.title');
    }

}
