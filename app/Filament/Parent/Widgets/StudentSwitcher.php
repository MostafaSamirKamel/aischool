<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

class StudentSwitcher extends Widget implements HasForms
{
    use HasWidgetShield, InteractsWithForms;

    protected string $view = 'filament.parent.widgets.student-switcher';
    
    protected int | string | array $columnSpan = 'full';

    public ?int $selectedStudent = null;

    public function mount(): void
    {
        $student = \App\Services\Parent\ParentStudentAccessService::getSelectedStudent();
        $this->selectedStudent = $student?->id;
    }

    public function getStudents(): array
    {
        return \App\Services\Parent\ParentStudentAccessService::getAuthorizedStudentsList();
    }

    public function switchStudent(int $studentId): void
    {
        if (\App\Services\Parent\ParentStudentAccessService::switchStudent($studentId)) {
            $this->selectedStudent = $studentId;
            $this->redirect(request()->header('Referer') ?? request()->url());
        }
    }
}
