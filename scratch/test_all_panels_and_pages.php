<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== REAL-WORLD PAGE-BY-PAGE AUDIT & VERIFICATION ===\n\n";

$rolesToTest = [
    'super_admin' => [
        'user' => User::where('is_super_admin', true)->first(),
        'label' => 'Super Administrator (المدير العام)',
        'panel' => 'admin',
        'routes' => [
            '/admin',
            '/admin/students',
            '/admin/enrollments',
            '/admin/groups',
            '/admin/courses',
            '/admin/classrooms',
            '/admin/session-schedules',
            '/admin/attendance-sessions',
            '/admin/homework',
            '/admin/exams',
            '/admin/exam-retake-grants',
            '/admin/payments',
            '/admin/installments',
            '/admin/vaults',
            '/admin/expenses',
            '/admin/salary-payments',
            '/admin/instructors',
            '/admin/educational-supervisors',
            '/admin/tenants',
            '/admin/users',
            '/admin/roles',
            '/admin/reports'
        ]
    ],
    'branch_manager_matamir' => [
        'user' => User::where('phone', '01070801308')->first(),
        'label' => 'Abu El Matamir Branch Manager (مدير فرع أبو المطامير)',
        'panel' => 'admin',
        'routes' => [
            '/admin',
            '/admin/students',
            '/admin/enrollments',
            '/admin/groups',
            '/admin/courses',
            '/admin/classrooms',
            '/admin/session-schedules',
            '/admin/attendance-sessions',
            '/admin/homework',
            '/admin/exams',
            '/admin/payments',
            '/admin/installments',
            '/admin/vaults',
            '/admin/salary-payments'
        ]
    ],
    'branch_manager_online' => [
        'user' => User::where('phone', '01501101213')->first(),
        'label' => 'Online Branch Manager (مدير فرع الأونلاين)',
        'panel' => 'admin',
        'routes' => [
            '/admin',
            '/admin/students',
            '/admin/groups',
            '/admin/enrollments'
        ]
    ],
    'instructor' => [
        'user' => User::where('phone', '01091812925')->first(),
        'label' => 'Lead Instructor (المعلم الرئيسي)',
        'panel' => 'instructor',
        'routes' => [
            '/instructor',
            '/instructor/groups',
            '/instructor/groups/94/academic-center',
            '/instructor/groups/95/academic-center',
            '/instructor/groups/96/academic-center',
            '/instructor/homework',
            '/instructor/exams',
            '/instructor/session-schedules',
            '/instructor/attendance-sessions',
            '/instructor/salary'
        ]
    ],
    'supervisor' => [
        'user' => User::where('phone', '01077777777')->first(),
        'label' => 'Educational Supervisor (المشرف التربوي)',
        'panel' => 'supervisor',
        'routes' => [
            '/supervisor',
            '/supervisor/group-profiles',
            '/supervisor/instructor-profiles',
            '/supervisor/educational-alerts'
        ]
    ],
    'student' => [
        'user' => User::where('phone', '01007287273')->first(),
        'label' => 'Primary Test Student (طالب أبو المطامير)',
        'panel' => 'student',
        'routes' => [
            '/student',
            '/student/my-courses',
            '/student/my-assignments',
            '/student/my-exams',
            '/student/my-attendance',
            '/student/my-payments',
            '/student/student-enrollments',
            '/student/student-learning-resources',
            '/student/student-reports'
        ]
    ],
    'parent' => [
        'user' => User::where('phone', '01009988771')->first(),
        'label' => 'Authorized Parent (ولي الأمر)',
        'panel' => 'parent',
        'routes' => [
            '/parent',
            '/parent/my-courses',
            '/parent/my-attendance',
            '/parent/my-payments',
            '/parent/student-reports'
        ]
    ]
];

$auditResults = [];
$totalTested = 0;
$totalPassed = 0;
$totalFailed = 0;

foreach ($rolesToTest as $roleKey => $rConfig) {
    $user = $rConfig['user'];
    $label = $rConfig['label'];
    echo "=========================================================\n";
    echo "ROLE: $label (User ID: " . ($user ? $user->id : 'NULL') . ")\n";
    echo "=========================================================\n";

    if (!$user) {
        echo "  [SKIP] User not found for role: $roleKey\n";
        continue;
    }

    Auth::login($user);

    foreach ($rConfig['routes'] as $uri) {
        $totalTested++;
        $fullUrl = 'http://127.0.0.1:8000' . $uri;

        // Perform internal subrequest via Laravel Http Kernel
        $req = Request::create($uri, 'GET');
        $req->setUserResolver(fn() => $user);
        
        try {
            $resp = $kernel->handle($req);
            $statusCode = $resp->getStatusCode();
            $content = $resp->getContent();
            $contentLen = strlen($content);

            $hasNavbar = str_contains($content, 'ai-employee-top-navbar') || str_contains($content, 'ai-student-mobile-suite') || str_contains($content, 'ai-parent-mobile-suite') || str_contains($content, 'fi-topbar') || str_contains($content, 'fi-sidebar');
            $hasTajawal = str_contains($content, 'Tajawal') || str_contains($content, 'custom-theme.css');
            $hasThemeSupport = str_contains($content, 'theme') || str_contains($content, 'dark');

            $status = ($statusCode === 200 || $statusCode === 302) ? 'PASS' : 'FAIL';
            if ($status === 'PASS') {
                $totalPassed++;
            } else {
                $totalFailed++;
            }

            $auditResults[] = [
                'role' => $roleKey,
                'role_label' => $label,
                'uri' => $uri,
                'status_code' => $statusCode,
                'status' => $status,
                'size_bytes' => $contentLen,
                'has_nav' => $hasNavbar,
                'has_tajawal' => $hasTajawal,
                'has_theme_support' => $hasThemeSupport
            ];

            echo sprintf("  [%s] %-42s -> HTTP %d (%d KB)\n", $status, $uri, $statusCode, round($contentLen / 1024));

            $kernel->terminate($req, $resp);
        } catch (\Throwable $e) {
            $totalFailed++;
            echo sprintf("  [FAIL] %-42s -> EXCEPTION: %s\n", $uri, $e->getMessage());
            $auditResults[] = [
                'role' => $roleKey,
                'role_label' => $label,
                'uri' => $uri,
                'status_code' => 500,
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
        }
    }
    echo "\n";
}

echo "=== AUDIT SUMMARY ===\n";
echo "Total Routes Tested: $totalTested\n";
echo "Total Passed: $totalPassed\n";
echo "Total Failed: $totalFailed\n";
echo "Success Rate: " . ($totalTested > 0 ? round(($totalPassed / $totalTested) * 100, 1) : 0) . "%\n";

file_put_contents(__DIR__ . '/page_audit_results.json', json_encode($auditResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "Saved detailed results to scratch/page_audit_results.json\n";
