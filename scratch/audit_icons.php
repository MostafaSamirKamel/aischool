<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use BladeUI\Icons\Factory as IconFactory;

$iconFactory = app(IconFactory::class);

$allIconUsages = [];
$brokenIcons = [];
$supportedIcons = [];

// 1. Scan PHP files in app/
$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path()));
foreach ($phpFiles as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $filePath = $file->getRealPath();
    $content = file_get_contents($filePath);

    // Regex for icon strings like 'heroicon-o-...', 'heroicon-s-...', 'heroicon-m-...', 'heroicon-c-...'
    if (preg_match_all("/(?:icon\(['\"]|navigationIcon\s*=\s*['\"]|emptyStateIcon\(['\"]|descriptionIcon\(['\"])(heroicon-[a-z0-9-]+)['\"]/i", $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as $match) {
            $iconName = $match[0];
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            
            $isValid = false;
            try {
                $svg = svg($iconName);
                $isValid = !empty($svg->toHtml());
            } catch (\Exception $e) {
                $isValid = false;
            }

            $entry = [
                'file' => str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath)),
                'line' => $line,
                'icon' => $iconName,
                'valid' => $isValid,
            ];

            $allIconUsages[] = $entry;
            if (!$isValid) {
                $brokenIcons[] = $entry;
            } else {
                $supportedIcons[$iconName] = ($supportedIcons[$iconName] ?? 0) + 1;
            }
        }
    }

    // Regex for Heroicon enum usages like Heroicon::Outlined... or Heroicon::...
    if (preg_match_all('/Heroicon::([A-Za-z0-9_]+)/', $content, $enumMatches, PREG_OFFSET_CAPTURE)) {
        foreach ($enumMatches[1] as $match) {
            $caseName = $match[0];
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;

            $isValid = false;
            $iconName = "Heroicon::{$caseName}";
            try {
                $enumRef = new ReflectionEnum(\Filament\Support\Icons\Heroicon::class);
                if ($enumRef->hasCase($caseName)) {
                    $case = $enumRef->getCase($caseName)->getValue();
                    $renderedName = $case->getIconForSize(\Filament\Support\Enums\IconSize::Medium);
                    $svg = svg($renderedName);
                    $isValid = !empty($svg->toHtml());
                    $iconName .= " -> {$renderedName}";
                }
            } catch (\Exception $e) {
                $isValid = false;
            }

            $entry = [
                'file' => str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath)),
                'line' => $line,
                'icon' => $iconName,
                'valid' => $isValid,
            ];

            $allIconUsages[] = $entry;
            if (!$isValid) {
                $brokenIcons[] = $entry;
            }
        }
    }

    // Also look for other string icons like ->icon('something-else')
    if (preg_match_all("/(?:->icon\(['\"]|navigationIcon\s*=\s*['\"])([^'\"]+)['\"]/i", $content, $genericMatches, PREG_OFFSET_CAPTURE)) {
        foreach ($genericMatches[1] as $match) {
            $iconName = trim($match[0]);
            if (str_starts_with($iconName, 'heroicon-')) continue; // already handled
            if (empty($iconName)) continue;

            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            $isValid = false;
            try {
                $svg = svg($iconName);
                $isValid = !empty($svg->toHtml());
            } catch (\Exception $e) {
                $isValid = false;
            }

            $entry = [
                'file' => str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath)),
                'line' => $line,
                'icon' => $iconName,
                'valid' => $isValid,
            ];

            $allIconUsages[] = $entry;
            if (!$isValid) {
                $brokenIcons[] = $entry;
            }
        }
    }
}

// 2. Scan Blade files in resources/views/
$bladeFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
foreach ($bladeFiles as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $filePath = $file->getRealPath();
    $content = file_get_contents($filePath);

    // Look for <x-heroicon-o-...>, <x-heroicon-s-...>, etc.
    if (preg_match_all('/<x-(heroicon-[a-z0-9-]+)/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as $match) {
            $iconName = $match[0];
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;

            $isValid = false;
            try {
                $svg = svg($iconName);
                $isValid = !empty($svg->toHtml());
            } catch (\Exception $e) {
                $isValid = false;
            }

            $entry = [
                'file' => str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath)),
                'line' => $line,
                'icon' => "<x-{$iconName}>",
                'valid' => $isValid,
            ];

            $allIconUsages[] = $entry;
            if (!$isValid) {
                $brokenIcons[] = $entry;
            } else {
                $supportedIcons[$iconName] = ($supportedIcons[$iconName] ?? 0) + 1;
            }
        }
    }

    // Look for <x-filament::icon :icon="'...'" or icon="..."
    if (preg_match_all('/<x-filament::icon[^>]*\bicon=[\'"]([^\'"]+)[\'"]/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as $match) {
            $iconName = trim($match[0]);
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;

            $isValid = false;
            try {
                $svg = svg($iconName);
                $isValid = !empty($svg->toHtml());
            } catch (\Exception $e) {
                $isValid = false;
            }

            $entry = [
                'file' => str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath)),
                'line' => $line,
                'icon' => "x-filament::icon({$iconName})",
                'valid' => $isValid,
            ];

            $allIconUsages[] = $entry;
            if (!$isValid) {
                $brokenIcons[] = $entry;
            }
        }
    }
}

echo "=== ICON AUDIT REPORT ===\n";
echo "Total Icon References Checked: " . count($allIconUsages) . "\n";
echo "Broken / Invalid Icons Found: " . count($brokenIcons) . "\n";
echo "Distinct Supported Icons: " . count($supportedIcons) . "\n\n";

if (!empty($brokenIcons)) {
    echo "--- BROKEN ICONS LIST ---\n";
    foreach ($brokenIcons as $b) {
        echo "File: {$b['file']}:{$b['line']} | Icon: {$b['icon']}\n";
    }
} else {
    echo "No broken icons found by basic regex check!\n";
}

file_put_contents(__DIR__ . '/audit_icons_result.json', json_encode([
    'total' => count($allIconUsages),
    'broken' => $brokenIcons,
    'supported_counts' => $supportedIcons,
    'all' => $allIconUsages,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
