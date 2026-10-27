<?php

$viewsDir = __DIR__ . '/../resources/views';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$bladeIconsDir = __DIR__ . '/../vendor/blade-ui-kit/blade-heroicons/resources/svg';
$validHeroicons = [];
if (is_dir($bladeHeroiconsDir)) {
    foreach (scandir($bladeHeroiconsDir) as $file) {
        if (str_ends_with($file, '.svg')) {
            $base = basename($file, '.svg');
            $validHeroicons["heroicon-{$base}"] = true;
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

$issues = [];
$totalSvgElements = 0;
$totalBladeIconComponents = 0;
$totalStudentIcons = 0;

foreach ($it as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $filePath = $file->getRealPath();
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $filePath);
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        $l = $lineNum + 1;
        
        // 1. Check <x-student-icon name="..." />
        if (preg_match_all('/<x-student-icon[^>]*name=[\'"]([^\'"]+)[\'"]/i', $line, $m)) {
            foreach ($m[1] as $name) {
                $totalStudentIcons++;
                if (!isset($studentIcons[$name])) {
                    $issues[] = [
                        'file' => $rel,
                        'line' => $l,
                        'type' => 'unknown_student_icon',
                        'name' => $name,
                        'detail' => "Icon '{$name}' not in student-icon dictionary, falls back to 'info'"
                    ];
                }
            }
        }

        // 2. Check <x-heroicon-... />
        if (preg_match_all('/<x-(heroicon-[a-z0-9\-]+)/i', $line, $m)) {
            foreach ($m[1] as $name) {
                $totalBladeIconComponents++;
                if (!isset($validHeroicons[$name])) {
                    $issues[] = [
                        'file' => $rel,
                        'line' => $l,
                        'type' => 'unknown_heroicon_component',
                        'name' => $name,
                        'detail' => "Blade component <x-{$name} /> not found in heroicons"
                    ];
                }
            }
        }

        // 3. Check <x-filament::icon icon="..." />
        if (preg_match_all('/<x-filament::icon[^>]*icon=[\'"]([^\'"]+)[\'"]/i', $line, $m)) {
            foreach ($m[1] as $name) {
                $totalBladeIconComponents++;
                if (!isset($validHeroicons[$name]) && !str_starts_with($name, 'fi-')) {
                    $issues[] = [
                        'file' => $rel,
                        'line' => $l,
                        'type' => 'unknown_filament_icon',
                        'name' => $name,
                        'detail' => "Filament icon '{$name}' not recognized"
                    ];
                }
            }
        }

        // 4. Raw SVG counts
        if (str_contains($line, '<svg')) {
            $totalSvgElements += substr_count($line, '<svg');
            
            // Check for missing viewBox
            if (!str_contains($line, 'viewBox') && !preg_match('/<svg[^>]*viewBox=/i', $line)) {
                // Check if multiline svg
                $remaining = substr($content, strpos($content, $line));
                if (preg_match('/<svg[^>]*>/i', $remaining, $svgTagM)) {
                    if (!str_contains($svgTagM[0], 'viewBox')) {
                        $issues[] = [
                            'file' => $rel,
                            'line' => $l,
                            'type' => 'svg_missing_viewbox',
                            'name' => 'svg',
                            'detail' => 'SVG tag missing viewBox attribute'
                        ];
                    }
                }
            }
        }
    }
}

echo "Total Raw SVGs in views: {$totalSvgElements}\n";
echo "Total <x-student-icon> calls: {$totalStudentIcons}\n";
echo "Total Blade icon components: {$totalBladeIconComponents}\n";
echo "Total Issues found: " . count($issues) . "\n\n";

foreach ($issues as $iss) {
    echo "[{$iss['type']}] {$iss['file']}:{$iss['line']} -> {$iss['name']} ({$iss['detail']})\n";
}

file_put_contents(__DIR__ . '/blade_views_audit.json', json_encode([
    'total_svgs' => $totalSvgElements,
    'total_student_icons' => $totalStudentIcons,
    'total_blade_icons' => $totalBladeIconComponents,
    'issues' => $issues
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
