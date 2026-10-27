<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bladeHeroiconsDir = __DIR__ . '/../vendor/blade-ui-kit/blade-heroicons/resources/svg';
$validHeroicons = [];
if (is_dir($bladeHeroiconsDir)) {
    $files = scandir($bladeHeroiconsDir);
    foreach ($files as $file) {
        if (str_ends_with($file, '.svg')) {
            $base = basename($file, '.svg');
            $validHeroicons["heroicon-{$base}"] = true;
        }
    }
}

$filamentSupportDir = __DIR__ . '/../vendor/filament/support/resources/svg';
$validFilamentIcons = [];
if (is_dir($filamentSupportDir)) {
    $files = scandir($filamentSupportDir);
    foreach ($files as $file) {
        if (str_ends_with($file, '.svg')) {
            $base = basename($file, '.svg');
            $validFilamentIcons["fi-{$base}"] = true;
        }
    }
}

// Student icon keys
$studentIconFile = __DIR__ . '/../resources/views/components/student-icon.blade.php';
$studentIcons = [];
if (file_exists($studentIconFile)) {
    $content = file_get_contents($studentIconFile);
    if (preg_match('/\$icons\s*=\s*\[(.*?)\];/s', $content, $matches)) {
        preg_match_all("/'([a-z0-9\-]+)'\s*=>/i", $matches[1], $kMatches);
        foreach ($kMatches[1] as $k) {
            $studentIcons[$k] = true;
        }
    }
}

echo "Loaded valid heroicons: " . count($validHeroicons) . "\n";
echo "Loaded valid filament icons: " . count($validFilamentIcons) . "\n";
echo "Loaded valid student-icon keys: " . count($studentIcons) . "\n\n";

// Scan files
$scanDirs = [
    __DIR__ . '/../app',
    __DIR__ . '/../resources/views',
    __DIR__ . '/../config',
];

$iconUsages = [];
$brokenOrMissing = [];
$studentIconMissing = [];

foreach ($scanDirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isDir()) continue;
        $ext = $file->getExtension();
        if (!in_array($ext, ['php', 'blade.php'])) continue;

        $path = $file->getRealPath();
        $relPath = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $path);
        $content = file_get_contents($path);

        // Pattern 1: Heroicons & Filament icons strings (e.g. 'heroicon-o-...', 'heroicon-s-...', 'heroicon-m-...', 'fi-...')
        if (preg_match_all('/[\'"]((heroicon-[a-z0-9\-]+)|(fi-[a-z0-9\-]+))[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $idx => $match) {
                $iconName = $match[0];
                $offset = $match[1];
                $line = substr_count(substr($content, 0, $offset), "\n") + 1;

                $isValid = isset($validHeroicons[$iconName]) || isset($validFilamentIcons[$iconName]);

                $iconUsages[] = [
                    'file' => $relPath,
                    'line' => $line,
                    'icon' => $iconName,
                    'valid' => $isValid,
                    'type' => 'blade-icon'
                ];

                if (!$isValid) {
                    $brokenOrMissing[] = [
                        'file' => $relPath,
                        'line' => $line,
                        'icon' => $iconName,
                        'reason' => 'Icon name not found in blade-heroicons or filament/support'
                    ];
                }
            }
        }

        // Pattern 2: Component tag: <x-heroicon-o-... /> etc.
        if (preg_match_all('/<x-(heroicon-[a-z0-9\-]+)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $idx => $match) {
                $iconName = $match[0];
                $offset = $match[1];
                $line = substr_count(substr($content, 0, $offset), "\n") + 1;

                $isValid = isset($validHeroicons[$iconName]);

                $iconUsages[] = [
                    'file' => $relPath,
                    'line' => $line,
                    'icon' => $iconName,
                    'valid' => $isValid,
                    'type' => 'blade-component'
                ];

                if (!$isValid) {
                    $brokenOrMissing[] = [
                        'file' => $relPath,
                        'line' => $line,
                        'icon' => $iconName,
                        'reason' => 'Blade component <x-' . $iconName . ' /> not found in blade-heroicons'
                    ];
                }
            }
        }

        // Pattern 3: <x-student-icon name="..." />
        if (preg_match_all('/<x-student-icon[^>]*name=[\'"]([^\'"]+)[\'"]/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $idx => $match) {
                $iconName = $match[0];
                $offset = $match[1];
                $line = substr_count(substr($content, 0, $offset), "\n") + 1;

                $isValid = isset($studentIcons[$iconName]);

                $iconUsages[] = [
                    'file' => $relPath,
                    'line' => $line,
                    'icon' => $iconName,
                    'valid' => $isValid,
                    'type' => 'student-icon'
                ];

                if (!$isValid) {
                    $studentIconMissing[] = [
                        'file' => $relPath,
                        'line' => $line,
                        'icon' => $iconName,
                        'reason' => "Icon key '{$iconName}' not defined in student-icon dictionary (falls back to info)"
                    ];
                }
            }
        }
    }
}

echo "Total icon references detected: " . count($iconUsages) . "\n";
echo "Broken/invalid heroicon/filament references: " . count($brokenOrMissing) . "\n";
echo "Missing student-icon dictionary keys: " . count($studentIconMissing) . "\n\n";

if (!empty($brokenOrMissing)) {
    echo "=== BROKEN / INVALID HEROICON & FILAMENT REFERENCES ===\n";
    foreach ($brokenOrMissing as $b) {
        echo "{$b['file']}:{$b['line']} -> {$b['icon']} ({$b['reason']})\n";
    }
    echo "\n";
}

if (!empty($studentIconMissing)) {
    echo "=== MISSING STUDENT-ICON KEYS (FALLING BACK TO 'info') ===\n";
    foreach ($studentIconMissing as $s) {
        echo "{$s['file']}:{$s['line']} -> '{$s['icon']}' ({$s['reason']})\n";
    }
    echo "\n";
}

file_put_contents(__DIR__ . '/audit_raw_results.json', json_encode([
    'total_usages' => count($iconUsages),
    'broken_or_missing' => $brokenOrMissing,
    'student_icon_missing' => $studentIconMissing,
    'all_usages' => $iconUsages
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Saved raw audit results to scratch/audit_raw_results.json\n";
