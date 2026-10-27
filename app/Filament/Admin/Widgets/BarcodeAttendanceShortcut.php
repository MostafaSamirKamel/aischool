<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Pages\BarcodeAttendance;
use Filament\Widgets\Widget;

class BarcodeAttendanceShortcut extends Widget
{
    protected string $view = 'filament.admin.widgets.barcode-attendance-shortcut';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']);
    }

    public function getUrl(): string
    {
        return BarcodeAttendance::getUrl();
    }
}
