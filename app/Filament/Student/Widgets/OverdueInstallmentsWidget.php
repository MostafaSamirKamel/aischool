<?php

namespace App\Filament\Student\Widgets;

use App\Models\Enrollment;
use App\Models\Installment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\View;

class OverdueInstallmentsWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.student.widgets.overdue-installments';

    protected int | string | array $columnSpan = 'full';

    public function getOverdueInstallments()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return collect();
        }

        $enrollmentIds = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->pluck('id');

        // Use actual paid_amount vs amount comparison, not stored status
        return Installment::whereIn('enrollment_id', $enrollmentIds)
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '<', now()->startOfDay())
            ->with(['enrollment.enrollmentCourses.course', 'enrollment.enrollmentCourses.group'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function hasOverdueInstallments(): bool
    {
        return $this->getOverdueInstallments()->isNotEmpty();
    }

    public static function canView(): bool
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return false;
        }

        $enrollmentIds = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->pluck('id');

        // Only show if truly overdue: past due date AND not fully paid
        return Installment::whereIn('enrollment_id', $enrollmentIds)
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', '<', now()->startOfDay())
            ->exists();
    }
}
