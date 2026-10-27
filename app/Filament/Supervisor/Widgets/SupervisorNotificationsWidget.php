<?php

namespace App\Filament\Supervisor\Widgets;

use App\Filament\Widgets\NotificationsWidget as BaseNotificationsWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SupervisorNotificationsWidget extends BaseNotificationsWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 0;
}
