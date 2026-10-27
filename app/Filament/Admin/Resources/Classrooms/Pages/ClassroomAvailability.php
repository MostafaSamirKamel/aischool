<?php

namespace App\Filament\Admin\Resources\Classrooms\Pages;

use App\Filament\Admin\Resources\Classrooms\ClassroomResource;
use App\Models\Classroom;
use App\Models\Tenant;
use App\Services\ClassroomAvailabilityService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;

class ClassroomAvailability extends Page
{
    protected static string $resource = ClassroomResource::class;

    protected string $view = 'filament.admin.resources.classrooms.pages.classroom-availability';

    protected static ?string $title = 'إدارة القاعات — الأوقات المتاحة والمشغولة';

    protected static ?string $navigationLabel = 'جدول توفر وإشغال القاعات';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 4;

    #[Url]
    public string $selectedDate = '';

    #[Url]
    public ?int $selectedClassroomId = null;

    #[Url]
    public ?int $selectedTenantId = null;

    #[Url]
    public string $viewMode = 'daily'; // 'daily' or 'weekly'

    public string $searchQuery = '';

    public string $filterStatus = 'all'; // 'all', 'with_available', 'fully_available', 'occupied_now'

    public function mount(): void
    {
        if (empty($this->selectedDate)) {
            $this->selectedDate = now()->format('Y-m-d');
        }

        $user = auth()->user();
        if (!$user->is_super_admin && $user->tenant_id) {
            $this->selectedTenantId = $user->tenant_id;
        }

        if (request()->has('classroom_id')) {
            $this->selectedClassroomId = (int) request()->get('classroom_id');
        }
    }

    public function getTitle(): string
    {
        return 'إدارة القاعات — الأوقات المتاحة والمشغولة';
    }

    public function getSubheading(): ?string
    {
        $dateFormatted = Carbon::parse($this->selectedDate)->locale('ar')->isoFormat('dddd، D MMMM Y');
        return "عرض دقيق لمواعيد إشغال وتوفر القاعات الدراسية ليوم {$dateFormatted} (ساعات العمل المعتمدة: 09:00 ص – 09:00 م)";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('قائمة القاعات')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(ClassroomResource::getUrl('index')),

            Action::make('today')
                ->label('اليوم')
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->action(function () {
                    $this->selectedDate = now()->format('Y-m-d');
                }),
        ];
    }

    public function changeDate(int $days): void
    {
        $current = Carbon::parse($this->selectedDate);
        $this->selectedDate = $current->addDays($days)->format('Y-m-d');
    }

    public function setDayOfWeek(string $dayOfWeek): void
    {
        $dayMap = [
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
        ];

        $targetDay = $dayMap[strtolower($dayOfWeek)] ?? Carbon::SATURDAY;
        $current = Carbon::parse($this->selectedDate);

        // Move to the day of week in the current week
        $startOfWeek = $current->copy()->startOfWeek(Carbon::SATURDAY);
        $diff = ($targetDay >= Carbon::SATURDAY) ? 0 : ($targetDay + 1);
        
        $newDate = $startOfWeek->copy();
        while ($newDate->dayOfWeek !== $targetDay) {
            $newDate->addDay();
        }

        $this->selectedDate = $newDate->format('Y-m-d');
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->viewMode = 'daily';
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['daily', 'weekly']) ? $mode : 'daily';
    }

    public function resetFilters(): void
    {
        $this->selectedClassroomId = null;
        $this->searchQuery = '';
        $this->filterStatus = 'all';
    }

    /**
     * Get Daily Availability Data.
     */
    public function getDailyAvailabilityProperty(): array
    {
        $service = app(ClassroomAvailabilityService::class);
        $data = $service->getAvailabilityForDate(
            $this->selectedDate,
            $this->selectedClassroomId,
            $this->selectedTenantId
        );

        // Filter by search query if provided
        if (!empty($this->searchQuery)) {
            $q = mb_strtolower($this->searchQuery, 'UTF-8');
            $data = array_filter($data, function ($item) use ($q) {
                return str_contains(mb_strtolower($item['classroom_name'], 'UTF-8'), $q) ||
                       str_contains(mb_strtolower($item['location'] ?? '', 'UTF-8'), $q);
            });
        }

        // Filter by occupancy status filter
        if ($this->filterStatus === 'with_available') {
            $data = array_filter($data, fn($item) => $item['is_active'] && !empty($item['available_slots']));
        } elseif ($this->filterStatus === 'fully_available') {
            $data = array_filter($data, fn($item) => $item['is_active'] && empty($item['occupied_slots']));
        } elseif ($this->filterStatus === 'has_bookings') {
            $data = array_filter($data, fn($item) => !empty($item['occupied_slots']));
        }

        return array_values($data);
    }

    /**
     * Get Weekly Availability Data.
     */
    public function getWeeklyAvailabilityProperty(): array
    {
        $service = app(ClassroomAvailabilityService::class);
        $data = $service->getWeeklyAvailability(
            $this->selectedDate,
            $this->selectedClassroomId,
            $this->selectedTenantId
        );

        if (!empty($this->searchQuery)) {
            $q = mb_strtolower($this->searchQuery, 'UTF-8');
            $data['classrooms'] = array_values(array_filter($data['classrooms'], function ($item) use ($q) {
                return str_contains(mb_strtolower($item['classroom_name'], 'UTF-8'), $q) ||
                       str_contains(mb_strtolower($item['location'] ?? '', 'UTF-8'), $q);
            }));
        }

        return $data;
    }

    /**
     * Classroom Select Options.
     */
    public function getClassroomsListProperty(): array
    {
        $user = auth()->user();
        $query = Classroom::query()->where('status', 'active');

        if (!$user->is_super_admin && $user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        } elseif ($this->selectedTenantId) {
            $query->where('tenant_id', $this->selectedTenantId);
        }

        return $query->orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Tenants list for Super Admin.
     */
    public function getTenantsListProperty(): array
    {
        if (!auth()->user()->is_super_admin) {
            return [];
        }

        return Tenant::pluck('name', 'id')->toArray();
    }
}
