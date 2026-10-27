<?php
require __DIR__ . '/../vendor/autoload.php';

$bladeHeroiconsDir = __DIR__ . '/../vendor/blade-ui-kit/blade-heroicons/resources/svg';
$svgs = scandir($bladeHeroiconsDir);

echo "Looking for calendar svgs:\n";
foreach ($svgs as $s) {
    if (str_contains($s, 'calendar')) {
        echo "  " . $s . "\n";
    }
}

echo "Looking for document svgs:\n";
foreach ($svgs as $s) {
    if ($s === 'o-document.svg' || $s === 'o-calendar.svg') {
        echo "  FOUND: " . $s . "\n";
    }
}
