<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Filament\Facades\Filament;
use App\Models\User;

$user = User::where('email', 'supervisor.main@admin.com')->first();
auth()->login($user);

$panel = Filament::getPanel('supervisor');
Filament::setCurrentPanel($panel);
$panel->boot();

echo "LOGGED IN: " . auth()->user()->name . "\n";
echo "PANEL: " . $panel->getId() . "\n";

foreach (Filament::getNavigation() as $group) {
    echo "Group: [" . ($group->getLabel() ?: 'Direct') . "]\n";
    foreach ($group->getItems() as $item) {
        echo "   - " . $item->getLabel() . " -> " . $item->getUrl() . "\n";
    }
}
