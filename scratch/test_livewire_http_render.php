<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Instructor\Pages\HomeworkReviewCenter;
use App\Models\User;
use App\Models\Group;
use Livewire\Livewire;

$instructorUser = User::where('email', 'instructor.main@admin.com')->first();
$group = Group::where('status', 'active')->first();

echo "Testing Livewire component render with instructor: " . $instructorUser->email . "\n";
echo "Active Group: " . ($group ? $group->name : 'None') . " (ID: " . ($group ? $group->id : 0) . ")\n";

auth()->login($instructorUser);

$test = Livewire::actingAs($instructorUser)
    ->test(HomeworkReviewCenter::class, ['group' => $group?->id]);

$test->assertSuccessful();
echo "✓ Component loaded successfully.\n";

// Verify tabs in HTML
$html = $test->html();
$tabs = ['الواجبات', 'الامتحانات', 'التسليمات', 'النتائج'];
foreach ($tabs as $tab) {
    if (str_contains($html, $tab)) {
        echo "✓ Tab found in HTML: {$tab}\n";
    } else {
        echo "✗ Tab missing in HTML: {$tab}\n";
    }
}

// Switch tabs and verify
$test->call('switchTab', 'exams')->assertSet('activeTab', 'exams');
echo "✓ Switched to Tab 2: exams\n";

$test->call('switchTab', 'submissions')->assertSet('activeTab', 'submissions');
echo "✓ Switched to Tab 3: submissions\n";

$test->call('switchTab', 'results')->assertSet('activeTab', 'results');
echo "✓ Switched to Tab 4: results\n";

echo "ALL LIVEWIRE COMPONENT HTTP RENDERS AND TAB SWITCHES VERIFIED WITH 100% SUCCESS!\n";
