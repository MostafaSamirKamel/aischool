<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Filament\Facades\Filament;
use App\Models\User;

$panels = ['admin', 'instructor', 'supervisor'];

// List available users with roles
echo "AVAILABLE USERS:\n";
foreach (User::with('roles')->get() as $u) {
    if ($u->roles->count()) {
        echo "- {$u->id}: {$u->name} ({$u->email}) -> " . $u->roles->pluck('name')->join(', ') . "\n";
    }
}
echo "\n";

$cases = [
    ['panel' => 'admin', 'email' => 'admin@admin.com', 'desc' => 'Super Admin (Admin Panel)'],
    ['panel' => 'admin', 'email' => 'manager.main@admin.com', 'desc' => 'Branch Manager (Admin Panel)'],
    ['panel' => 'admin', 'email' => 'secretary.main@admin.com', 'desc' => 'Secretary (Admin Panel)'],
    ['panel' => 'instructor', 'email' => 'instructor.main@admin.com', 'desc' => 'Instructor (Instructor Panel)'],
    ['panel' => 'supervisor', 'email' => 'supervisor.main@admin.com', 'desc' => 'Supervisor (Supervisor Panel)'],
];

foreach ($cases as $c) {
    auth()->logout();
    echo "====================================================\n";
    echo "ROLE: {$c['desc']}\n";
    echo "====================================================\n";
    
    $panel = Filament::getPanel($c['panel']);
    Filament::setCurrentPanel($panel);
    
    $user = User::where('email', $c['email'])->first();
    if ($user) {
        auth()->login($user);
        echo "Logged in as: {$user->name} ({$user->email})\n";
    }
    
    $nav = Filament::getNavigation();
    foreach ($nav as $group) {
        $groupLabel = $group->getLabel() ?: 'بدون مجموعة (Direct / Root)';
        echo "Group: [{$groupLabel}] (" . count($group->getItems()) . " items)\n";
        foreach ($group->getItems() as $item) {
            echo "   - " . $item->getLabel() . " (" . $item->getUrl() . ")\n";
        }
    }
    echo "\n";
}
