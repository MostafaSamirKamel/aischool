<?php
require __DIR__ . '/../vendor/autoload.php';

$cases = \Filament\Support\Icons\Heroicon::cases();
echo "Total Heroicon enum cases: " . count($cases) . "\n";
echo "Sample cases:\n";
for ($i = 0; $i < 10; $i++) {
    echo $cases[$i]->name . " => " . $cases[$i]->value . "\n";
}

// Check for CheckCircle
foreach ($cases as $c) {
    if (str_contains($c->name, 'Check')) {
        echo "Found case: {$c->name} => {$c->value}\n";
    }
}
