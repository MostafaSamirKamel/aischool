<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== DOM & HTTP RENDERING AUDIT ===\n\n";

$panels = [
    'admin' => ['route' => '/admin/login', 'email' => 'admin@admin.com', 'role' => 'super_admin'],
    'instructor' => ['route' => '/instructor/login', 'email' => 'instructor.main@admin.com', 'role' => 'instructor'],
    'supervisor' => ['route' => '/supervisor/login', 'email' => 'supervisor.main@admin.com', 'role' => 'supervisor'],
    'student' => ['route' => '/student/login', 'email' => 'student1.6ad507ba156f9@school.test', 'role' => 'student'],
    'parent' => ['route' => '/parent/login', 'email' => 'parent2.6ad507ba5fa75@school.test', 'role' => 'parent'],
];

$results = [];

foreach ($panels as $panelName => $info) {
    echo "Auditing Panel: {$panelName} ({$info['route']})...\n";
    
    // Test HTTP status of login page
    $loginHtml = @file_get_contents('http://127.0.0.1:8000' . $info['route']);
    $loginHttpCode = $http_response_header[0] ?? 'UNKNOWN';
    
    // Check asset links in login page
    $hasCustomTheme = str_contains($loginHtml, 'custom-theme.css');
    $hasFilamentAdminTheme = str_contains($loginHtml, 'filament-admin-theme.css');
    
    // Now simulate authenticated page load via Laravel test kernel / request
    $user = User::where('email', $info['email'])->first();
    $authCheck = 'N/A';
    $renderedHome = '';
    
    if ($user) {
        Auth::login($user);
        $request = \Illuminate\Http\Request::create('/' . $panelName, 'GET');
        $response = $app->handle($request);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        
        $hasThemeToggle = str_contains($content, 'ai-theme-toggle-btn');
        $hasTajawalFont = str_contains($content, 'Tajawal') || str_contains($content, 'tajawal');
        $hasMobileNav = str_contains($content, 'ai-parent-mobile-suite') || str_contains($content, 'ai-student-mobile-suite');
        $hasChildrenSelector = str_contains($content, 'ChildrenSelectorWidget') || str_contains($content, 'اختر الطالب');
        
        $results[$panelName] = [
            'login_http' => $loginHttpCode,
            'login_custom_theme' => $hasCustomTheme ? 'YES' : 'NO',
            'auth_status_code' => $statusCode,
            'has_theme_toggle' => $hasThemeToggle ? 'YES' : 'NO',
            'has_tajawal_font' => $hasTajawalFont ? 'YES' : 'NO',
            'has_mobile_nav' => $hasMobileNav ? 'YES' : 'NO',
            'has_children_selector' => $hasChildrenSelector ? 'YES' : 'NO',
            'html_length' => strlen($content),
        ];
    } else {
        $results[$panelName] = ['error' => 'User not found'];
    }
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
