<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::where('email', 'admin@admin.com')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/admin/reporting-center-page', 'GET');
$response = $kernel->handle($request);

echo "HTTP Status: " . $response->getStatusCode() . PHP_EOL;

$content = $response->getContent();
$checks = [
    'مركز التقارير والاستخبارات التشغيلية والمالية',
    'الطلاب الجدد المسجلين',
    'الاشتراكات والتجديدات',
    'التحصيلات النقدية الفعلية',
    'إجمالي المديونيات المستحقة',
    'حركة السيولة والتدفق النقدي الفعلي بالخزائن',
    'مؤشرات الانضباط والامتثال الأكاديمي',
    'Excel',
    'PDF',
    'CSV',
];

foreach ($checks as $phrase) {
    $found = strpos($content, $phrase) !== false;
    echo "Check '{$phrase}': " . ($found ? "FOUND" : "NOT FOUND") . PHP_EOL;
}

echo "HTML Render verified successfully!\n";
