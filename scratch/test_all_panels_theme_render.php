<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$consoleKernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();
$httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use App\Models\Student;
use App\Models\Instructor;
use Illuminate\Support\Facades\Auth;

echo "=========================================================================\n";
echo "AI SCHOOL — TEST ALL PANELS THEME RENDER (LIGHT & DARK SYSTEM)\n";
echo "=========================================================================\n\n";

$instructorUser = User::where('email', 'instructor.a@aischool.test')->first();
$studentUser = User::where('email', 'student.a@aischool.test')->first();
$parentUser = User::where('email', 'parent.a@aischool.test')->first();
$adminUser = User::where('email', 'admin@admin.com')->first();

$tests = [
    [
        'panel' => 'Admin Dashboard',
        'user' => $adminUser,
        'url' => '/admin',
    ],
    [
        'panel' => 'Instructor Dashboard',
        'user' => $instructorUser,
        'url' => '/instructor',
    ],
    [
        'panel' => 'Instructor Unified Academic Center',
        'user' => $instructorUser,
        'url' => '/instructor/homework-review-center',
    ],
    [
        'panel' => 'Student Dashboard',
        'user' => $studentUser,
        'url' => '/student',
    ],
    [
        'panel' => 'Student Assignments',
        'user' => $studentUser,
        'url' => '/student/my-assignments',
    ],
    [
        'panel' => 'Student Exams',
        'user' => $studentUser,
        'url' => '/student/my-exams',
    ],
    [
        'panel' => 'Parent Dashboard',
        'user' => $parentUser,
        'url' => '/parent',
    ],
    [
        'panel' => 'Parent Student Reports',
        'user' => $parentUser,
        'url' => '/parent/student-reports',
    ],
];

$pass = 0;
$fail = 0;

foreach ($tests as $t) {
    if (!$t['user']) {
        echo " [SKIP] {$t['panel']} -> User not found\n";
        continue;
    }

    $panelId = explode('/', trim($t['url'], '/'))[0];
    try {
        $panel = \Filament\Facades\Filament::getPanel($panelId);
        \Filament\Facades\Filament::setCurrentPanel($panel);
        \Filament\Facades\Filament::auth()->login($t['user']);
    } catch (\Throwable $e) {
        Auth::login($t['user']);
    }
    
    $request = \Illuminate\Http\Request::create($t['url'], 'GET');
    $request->headers->set('Accept', 'text/html');
    
    $session = $app->make('session')->driver();
    $session->start();
    $session->put(Auth::getName(), $t['user']->getAuthIdentifier());
    $session->save();
    $request->setLaravelSession($session);
    $request->cookies->set($session->getName(), $session->getId());
    
    try {
        $response = $httpKernel->handle($request);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $httpKernel->terminate($request, $response);
        
        $hasCustomTheme = str_contains($content, 'custom-theme.css');
        $hasToggleBtn = str_contains($content, 'ai-theme-toggle-btn');
        $hasAntiFlash = str_contains($content, 'localStorage.getItem("theme")');
        
        if ($status >= 200 && $status < 400 && $hasCustomTheme && $hasToggleBtn && $hasAntiFlash) {
            echo " [PASS] {$t['panel']} ({$t['url']}) -> HTTP $status | Theme: Verified (Anti-flash + Toggle + CSS)\n";
            $pass++;
        } else {
            $loc = $response->headers->get('Location');
            echo " [FAIL] {$t['panel']} ({$t['url']}) -> HTTP $status" . ($loc ? " -> Redirect to: $loc" : "") . " | Theme: CustomTheme=" . ($hasCustomTheme ? 'Y' : 'N') . " Toggle=" . ($hasToggleBtn ? 'Y' : 'N') . " AntiFlash=" . ($hasAntiFlash ? 'Y' : 'N') . "\n";
            $fail++;
        }
    } catch (\Throwable $e) {
        echo " [ERROR] {$t['panel']} ({$t['url']}) -> Exception: " . $e->getMessage() . "\n";
        $fail++;
    }
}

echo "\n-------------------------------------------------------------------------\n";
echo "PANEL THEME VERIFICATION: $pass Passed | $fail Failed\n";
echo "-------------------------------------------------------------------------\n";

exit($fail > 0 ? 1 : 0);
