<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class OverdueInstallmentsWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.parent.widgets.overdue-installments';

    protected int | string | array $columnSpan = 'full';

    public function getOverdueInstallments()
    {
        $selectedStudentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
        
        if ($selectedStudentId) {
            $studentIds = [$selectedStudentId];
        } else {
            $user = auth()->user();
            if (!$user) {
                return collect();
            }
            $studentIds = $user->students()->pluck('students.id')->toArray();
        }

        if (empty($studentIds)) {
            return collect();
        }

        $enrollmentIds = Enrollment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->pluck('id');

        return Installment::whereIn('enrollment_id', $enrollmentIds)
            ->overdue()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function hasOverdueInstallments(): bool
    {
        return $this->getOverdueInstallments()->isNotEmpty();
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $studentIds = $user->students()->pluck('students.id')->toArray();
        if (empty($studentIds)) {
            return false;
        }

        $enrollmentIds = Enrollment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->pluck('id');

        return Installment::whereIn('enrollment_id', $enrollmentIds)
            ->overdue()
            ->exists();
    }
}
