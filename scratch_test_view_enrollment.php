<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Admin\Resources\Enrollments\Pages\ViewEnrollment;
use App\Models\Enrollment;
use App\Models\User;
use Livewire\Livewire;

$user = User::where('is_super_admin', true)->first();
$enrollment = Enrollment::first();

echo "User: " . ($user ? $user->email : 'none') . "\n";
echo "Enrollment ID: " . ($enrollment ? $enrollment->id : 'none') . "\n";

try {
    $component = Livewire::actingAs($user)->test(ViewEnrollment::class, ['record' => $enrollment->id]);
    echo "Component mounted successfully! Actions available:\n";
    $actions = $component->instance()->getCachedHeaderActions();
    foreach ($actions as $action) {
        echo "- " . $action->getName() . " (visible: " . ($action->isVisible() ? 'yes' : 'no') . ")\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
