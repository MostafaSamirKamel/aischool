<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$testIcons = [
    'heroicon-o-user-group',
    'o-user-group',
    'home',
    \Filament\Support\Icons\Heroicon::OutlinedUserGroup,
    \Filament\Support\Icons\Heroicon::AcademicCap,
];

foreach ($testIcons as $t) {
    $label = is_object($t) ? get_class($t) . '::' . $t->name : $t;
    try {
        $html = \Filament\Support\generate_icon_html($t);
        $result = $html ? 'OK (' . strlen($html->toHtml()) . ' bytes)' : 'NULL';
    } catch (\Throwable $e) {
        $result = 'ERROR: ' . $e->getMessage();
    }
    echo "{$label} -> {$result}\n";
}
