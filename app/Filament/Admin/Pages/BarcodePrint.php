<?php

namespace App\Filament\Admin\Pages;

use App\Models\Admin;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BarcodePrint extends Page
{
    protected string $view = 'filament.admin.pages.barcode-print';

    protected static ?string $navigationLabel = 'طباعة الباركود';

    protected static ?string $title = 'طباعة الباركود';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-printer';

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.students_and_registration');
    }

    public string $type = 'students';

    public ?string $search = null;

    public int $limit = 60;

    public string $studentScope = 'count';

    public ?string $selectedStudentId = null;

    public ?string $selectedGroupId = null;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->is_super_admin || $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']));
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']), 403);

        $type = (string) request()->query('type', '');
        $scope = (string) request()->query('student_scope', '');
        $studentId = request()->query('student_id');

        if (in_array($type, ['students', 'instructors', 'secretaries'], true)) {
            $this->type = $type;
        }

        if (in_array($scope, ['count', 'single', 'group'], true)) {
            $this->studentScope = $scope;
        }

        if ($studentId !== null && $studentId !== '') {
            $this->selectedStudentId = (string) $studentId;
            $this->type = 'students';
            $this->studentScope = 'single';
        }
    }

    public function updatedType(): void
    {
        if ($this->type !== 'students') {
            $this->studentScope = 'count';
            $this->selectedStudentId = null;
            $this->selectedGroupId = null;
        }
    }

    public function updatedStudentScope(): void
    {
        $this->selectedStudentId = null;
        $this->selectedGroupId = null;
    }

    public function getItems(): Collection
    {
        $search = trim((string) $this->search);

        if ($this->type === 'instructors') {
            $query = Instructor::query()->with('user');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                    });
            }

            return $query->orderBy('employee_code')->limit($this->limit)->get();
        }

        if ($this->type === 'secretaries') {
            $query = Admin::query()->with('user')
                ->whereHas('user.roles', function ($q) {
                    $q->where('name', 'secretary');
                });

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                    });
            }

            return $query->orderBy('employee_code')->limit($this->limit)->get();
        }

        $query = Student::query()->with('user');

        if ($this->studentScope === 'single' && !$this->selectedStudentId) {
            return collect();
        }

        if ($this->studentScope === 'group' && !$this->selectedGroupId) {
            return collect();
        }

        if ($this->studentScope === 'single' && $this->selectedStudentId) {
            $query->whereKey((int) $this->selectedStudentId);
        }

        if ($this->studentScope === 'group' && $this->selectedGroupId) {
            $group = Group::query()->find((int) $this->selectedGroupId);

            if ($group) {
                $studentIds = $group->activeStudents()->pluck('students.id');
                $query->whereIn('students.id', $studentIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('student_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
                });
        }

        if ($this->studentScope === 'single') {
            return $query->orderBy('student_code')->limit(1)->get();
        }

        if ($this->studentScope === 'group') {
            return $query->orderBy('student_code')->get();
        }

        return $query->orderBy('student_code')->limit($this->limit)->get();
    }

    public function getStudentOptions(): array
    {
        return Student::query()
            ->with('user')
            ->orderBy('student_code')
            ->limit(1000)
            ->get()
            ->mapWithKeys(function (Student $student) {
                $name = $student->user?->name ?? 'غير محدد';
                return [$student->id => ($student->student_code ?? '---') . ' - ' . $name];
            })
            ->toArray();
    }

    public function getGroupOptions(): array
    {
        return Group::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
