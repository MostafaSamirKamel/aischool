<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Widgets\NotificationsWidget as BaseNotificationsWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AdminNotificationsWidget extends BaseNotificationsWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;
}
