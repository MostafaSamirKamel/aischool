<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=========================================================================\n";
echo "AI SCHOOL — VERIFY COMPLETE COLOR SYSTEM, DARK MODE & RESPONSIVENESS\n";
echo "=========================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck($name, $condition, &$passCount, &$failCount) {
    if ($condition) {
        echo " [PASS] $name\n";
        $passCount++;
    } else {
        echo " [FAIL] $name\n";
        $failCount++;
    }
}

// 1. Check custom-theme.css tokens
$customTheme = file_get_contents(public_path('css/custom-theme.css'));
assertCheck("custom-theme.css contains Section 23 Navy #0F172A", str_contains($customTheme, '#0F172A'), $passCount, $failCount);
assertCheck("custom-theme.css contains Primary Blue #2563EB", str_contains($customTheme, '#2563EB'), $passCount, $failCount);
assertCheck("custom-theme.css contains Secondary Blue #3B82F6", str_contains($customTheme, '#3B82F6'), $passCount, $failCount);
assertCheck("custom-theme.css contains Teal #0F766E", str_contains($customTheme, '#0F766E'), $passCount, $failCount);
assertCheck("custom-theme.css contains Success #16A34A", str_contains($customTheme, '#16A34A'), $passCount, $failCount);
assertCheck("custom-theme.css contains Warning #D97706", str_contains($customTheme, '#D97706'), $passCount, $failCount);
assertCheck("custom-theme.css contains Error #DC2626", str_contains($customTheme, '#DC2626'), $passCount, $failCount);
assertCheck("custom-theme.css contains Information #0284C7", str_contains($customTheme, '#0284C7'), $passCount, $failCount);
assertCheck("custom-theme.css contains Dark Main Background #0B1220", str_contains($customTheme, '#0B1220'), $passCount, $failCount);
assertCheck("custom-theme.css contains Dark Card Background #172033", str_contains($customTheme, '#172033'), $passCount, $failCount);
assertCheck("custom-theme.css contains Dark Elevated Surface #1E293B", str_contains($customTheme, '#1E293B'), $passCount, $failCount);
assertCheck("custom-theme.css contains Dark Text Primary #F1F5F9", str_contains($customTheme, '#F1F5F9'), $passCount, $failCount);
assertCheck("custom-theme.css contains Dark Text Secondary #CBD5E1", str_contains($customTheme, '#CBD5E1'), $passCount, $failCount);
assertCheck("custom-theme.css contains Dark Border #334155", str_contains($customTheme, '#334155'), $passCount, $failCount);
assertCheck("custom-theme.css contains Section 11 Professional Dark Mode", str_contains($customTheme, '11. PROFESSIONAL DARK MODE'), $passCount, $failCount);
assertCheck("custom-theme.css contains html.dark body selectors", str_contains($customTheme, 'html.dark body'), $passCount, $failCount);
assertCheck("custom-theme.css contains mobile responsive 360px query", str_contains($customTheme, '@media (max-width: 360px)'), $passCount, $failCount);

// 2. Check student-theme-scoped.css
$studentTheme = file_get_contents(public_path('css/student-theme-scoped.css'));
assertCheck("student-theme-scoped.css has dark mode wrapper", str_contains($studentTheme, '.dark .student-theme-wrapper'), $passCount, $failCount);
assertCheck("student-theme-scoped.css uses #0B1220 bg", str_contains($studentTheme, '--bg: #0B1220'), $passCount, $failCount);
assertCheck("student-theme-scoped.css uses #172033 card paper", str_contains($studentTheme, '--paper: #172033'), $passCount, $failCount);
assertCheck("student-theme-scoped.css uses #F1F5F9 ink text", str_contains($studentTheme, '--ink: #F1F5F9'), $passCount, $failCount);
assertCheck("student-theme-scoped.css uses #334155 border line", str_contains($studentTheme, '--line: #334155'), $passCount, $failCount);

// 3. Check shared report-theme.blade.php
$reportTheme = file_get_contents(resource_path('views/filament/shared/report-theme.blade.php'));
assertCheck("report-theme has .dark .report-shell", str_contains($reportTheme, '.dark .report-shell'), $passCount, $failCount);
assertCheck("report-theme has dark table header #1E293B", str_contains($reportTheme, '#1E293B'), $passCount, $failCount);
assertCheck("report-theme has dark card #172033", str_contains($reportTheme, '#172033'), $passCount, $failCount);

// 4. Check take-exam.blade.php
$takeExam = file_get_contents(resource_path('views/filament/student/pages/take-exam.blade.php'));
assertCheck("take-exam has .dark .te-sticky", str_contains($takeExam, '.dark .te-sticky'), $passCount, $failCount);
assertCheck("take-exam has .dark .te-question", str_contains($takeExam, '.dark .te-question'), $passCount, $failCount);
assertCheck("take-exam has .dark .te-option", str_contains($takeExam, '.dark .te-option'), $passCount, $failCount);

// 5. Check exam-result.blade.php
$examResult = file_get_contents(resource_path('views/filament/student/pages/exam-result.blade.php'));
assertCheck("exam-result has .dark .result-stat-box", str_contains($examResult, '.dark .result-stat-box'), $passCount, $failCount);
assertCheck("exam-result has .dark .question-card", str_contains($examResult, '.dark .question-card'), $passCount, $failCount);

// 6. Check Panel Providers
$adminProvider = file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));
$instructorProvider = file_get_contents(app_path('Providers/Filament/InstructorPanelProvider.php'));
$supervisorProvider = file_get_contents(app_path('Providers/Filament/SupervisorPanelProvider.php'));
$studentProvider = file_get_contents(app_path('Providers/Filament/StudentPanelProvider.php'));
$parentProvider = file_get_contents(app_path('Providers/Filament/ParentPanelProvider.php'));

assertCheck("AdminPanelProvider has darkMode(true)", str_contains($adminProvider, 'darkMode(true)'), $passCount, $failCount);
assertCheck("InstructorPanelProvider has darkMode(true)", str_contains($instructorProvider, 'darkMode(true)'), $passCount, $failCount);
assertCheck("SupervisorPanelProvider has darkMode(true)", str_contains($supervisorProvider, 'darkMode(true)'), $passCount, $failCount);
assertCheck("StudentPanelProvider has darkMode(true)", str_contains($studentProvider, 'darkMode(true)'), $passCount, $failCount);
assertCheck("ParentPanelProvider has darkMode(true)", str_contains($parentProvider, 'darkMode(true)'), $passCount, $failCount);

assertCheck("All providers have anti-flash livewire preserver", 
    str_contains($adminProvider, 'livewire:navigated') &&
    str_contains($instructorProvider, 'livewire:navigated') &&
    str_contains($supervisorProvider, 'livewire:navigated') &&
    str_contains($studentProvider, 'livewire:navigated') &&
    str_contains($parentProvider, 'livewire:navigated'),
    $passCount, $failCount
);

assertCheck("All providers have accessible theme toggle button", 
    str_contains($adminProvider, 'ai-theme-toggle-btn') &&
    str_contains($instructorProvider, 'ai-theme-toggle-btn') &&
    str_contains($supervisorProvider, 'ai-theme-toggle-btn') &&
    str_contains($studentProvider, 'ai-theme-toggle-btn') &&
    str_contains($parentProvider, 'ai-theme-toggle-btn'),
    $passCount, $failCount
);

assertCheck("Student and Parent have mobile-navigation included", 
    str_contains($studentProvider, 'filament.student.components.mobile-navigation') &&
    str_contains($parentProvider, 'filament.parent.components.mobile-navigation'),
    $passCount, $failCount
);

echo "\n-------------------------------------------------------------------------\n";
echo "SUMMARY: Passed: $passCount | Failed: $failCount\n";
echo "-------------------------------------------------------------------------\n";

exit($failCount > 0 ? 1 : 0);
