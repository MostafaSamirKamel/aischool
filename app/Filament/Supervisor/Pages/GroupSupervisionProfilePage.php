<?php

namespace App\Filament\Supervisor\Pages;

use App\Models\Group;
use App\Services\EducationalSupervisionService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class GroupSupervisionProfilePage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected string $view = 'filament.supervisor.pages.group-profiles';

    protected static ?string $slug = 'group-profiles';

    protected static ?string $navigationLabel = 'الملفات الإشرافية للمجموعات';

    protected static ?string $title = 'الملف الإشرافي والأكاديمي للمجموعة';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return 'الإشراف التعليمي';
    }

    public ?int $selectedGroupId = null;
    public ?array $groupProfileData = null;
    public Collection $groupsList;

    public function mount(): void
    {
        $service = app(EducationalSupervisionService::class);
        $user = auth()->user();
        $groupIds = $service->getSupervisedGroupIds($user);

        $this->groupsList = Group::whereIn('id', $groupIds)
            ->with(['course', 'instructor.user'])
            ->get();

        if (request()->has('group_id')) {
            $this->selectGroup((int) request()->get('group_id'));
        } elseif ($this->groupsList->isNotEmpty()) {
            $this->selectGroup($this->groupsList->first()->id);
        }
    }

    public function selectGroup(int $id): void
    {
        $this->selectedGroupId = $id;
        $service = app(EducationalSupervisionService::class);
        $this->groupProfileData = $service->getGroupSupervisionProfile(auth()->user(), $id);
    }
}
