<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Reporting\ReportingExportService;

$export = app(ReportingExportService::class);

$headers = ['الكود', 'الاسم', 'المبلغ', 'الحالة'];
$rows = [
    ['=1+1', 'طالب تجريبي أ', '1500.00', 'نشط'],
    ['STU-02', 'طالب تجريبي ب', '2000.00', 'متأخر'],
];

// Test Formula sanitization
$sanitized = ReportingExportService::sanitizeCell('=1+1');
if ($sanitized !== "'=1+1") {
    throw new Exception("Formula sanitization failed: " . $sanitized);
}
echo "Formula sanitization: PASS ('=1+1 -> " . $sanitized . ")\n";

// Test PDF export
$pdf = $export->exportPdf('تقرير تجريبي', $headers, $rows, ['branch_name' => 'فرع القاهرة', 'period_label' => 'أكتوبر 2026']);
if (empty($pdf) || strlen($pdf) < 100) {
    throw new Exception("PDF export failed or empty");
}
echo "PDF Export: PASS (" . strlen($pdf) . " bytes generated)\n";

echo "ALL EXPORT TESTS PASSED!\n";
