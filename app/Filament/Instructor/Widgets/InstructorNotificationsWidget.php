<?php

namespace App\Filament\Instructor\Widgets;

use App\Filament\Widgets\NotificationsWidget as BaseNotificationsWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class InstructorNotificationsWidget extends BaseNotificationsWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 0;
}
