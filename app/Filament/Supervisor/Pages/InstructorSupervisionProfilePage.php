<?php

namespace App\Filament\Supervisor\Pages;

use App\Models\Instructor;
use App\Services\EducationalSupervisionService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class InstructorSupervisionProfilePage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected string $view = 'filament.supervisor.pages.instructor-profiles';

    protected static ?string $slug = 'instructor-profiles';

    protected static ?string $navigationLabel = 'الملفات الإشرافية للمهندسين';

    protected static ?string $title = 'الملف الإشرافي والأكاديمي للمهندس';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'الإشراف التعليمي';
    }

    public ?int $selectedInstructorId = null;
    public ?array $profileData = null;
    public Collection $instructorsList;

    public function mount(): void
    {
        $service = app(EducationalSupervisionService::class);
        $user = auth()->user();
        $instructorIds = $service->getSupervisedInstructorIds($user);

        $this->instructorsList = Instructor::whereIn('id', $instructorIds)
            ->with(['user', 'department'])
            ->get();

        if (request()->has('instructor_id')) {
            $this->selectInstructor((int) request()->get('instructor_id'));
        } elseif ($this->instructorsList->isNotEmpty()) {
            $this->selectInstructor($this->instructorsList->first()->id);
        }
    }

    public function selectInstructor(int $id): void
    {
        $this->selectedInstructorId = $id;
        $service = app(EducationalSupervisionService::class);
        $this->profileData = $service->getInstructorSupervisionProfile(auth()->user(), $id);
    }
}
