<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Student;
use App\Models\StudentParent;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class ChildrenSelectorWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.parent.widgets.children-selector';

    public $selectedChildId;
    public $children = [];

    public function mount(): void
    {
        $user = auth()->user();
        
        // Get all students linked to this parent through student_parents pivot table
        $studentIds = StudentParent::where('user_id', $user->id)
            ->pluck('student_id')
            ->toArray();

        if (!empty($studentIds)) {
            $this->children = Student::whereIn('id', $studentIds)
                ->with('user')
                ->get()
                ->map(fn($student) => [
                    'id' => $student->id,
                    'name' => $student->user->name ?? 'الطالب',
                    'class' => $student->class_level ?? 'غير محدد',
                ]);

            $this->selectedChildId = $this->children->first()['id'] ?? null;
        }
    }

    public function selectChild($childId): void
    {
        $this->selectedChildId = $childId;
        
        // Emit event to update all child widgets
        $this->dispatch('childSelected', childId: $childId);
    }
}
