<?php

namespace App\Filament\Supervisor\Widgets;

use App\Services\EducationalSupervisionService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class DailyPriorityQueueWidget extends Widget
{
    protected string $view = 'filament.supervisor.widgets.daily-priority-queue-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getPriorityQueue(): Collection
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        $service = app(EducationalSupervisionService::class);
        return $service->getDailyPriorityQueue($user);
    }
}
