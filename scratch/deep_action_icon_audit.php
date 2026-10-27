<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bladeHeroiconsDir = __DIR__ . '/../vendor/blade-ui-kit/blade-heroicons/resources/svg';
$validHeroicons = [];
if (is_dir($bladeHeroiconsDir)) {
    foreach (scandir($bladeHeroiconsDir) as $file) {
        if (str_ends_with($file, '.svg')) {
            $base = basename($file, '.svg');
            $validHeroicons["heroicon-{$base}"] = true;
            // Also store short names like o-..., s-..., m-..., c-...
            $validHeroicons[$base] = true;
        }
    }
}

// Find all PHP files in app/Filament
$dir = new RecursiveDirectoryIterator(__DIR__ . '/../app/Filament');
$it = new RecursiveIteratorIterator($dir);

$actionIcons = [];
$buttonIssues = [];
$genericIcons = [];
$tableActions = [];

foreach ($it as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $filePath = $file->getRealPath();
    $relPath = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $filePath);
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        $l = $lineNum + 1;
        // Search for ->icon(...)
        if (preg_match_all('/->icon\s*\(\s*([^)]+)\s*\)/', $line, $m)) {
            foreach ($m[1] as $rawIcon) {
                $rawIcon = trim($rawIcon);
                $actionIcons[] = [
                    'file' => $relPath,
                    'line' => $l,
                    'raw' => $rawIcon,
                    'context' => trim($line)
                ];
            }
        }

        // Search for Action::make(...) without icon, or emoji in label
        if (preg_match('/Action::make\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $m)) {
            $actionName = $m[1];
            $tableActions[] = [
                'file' => $relPath,
                'line' => $l,
                'action' => $actionName,
            ];
        }

        // Search for emoji in labels
        if (preg_match('/->label\s*\(\s*[\'"]([^\'"]*[\x{1F300}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}][^\'"]*)[\'"]\s*\)/u', $line, $m)) {
            $buttonIssues[] = [
                'file' => $relPath,
                'line' => $l,
                'type' => 'emoji_in_label',
                'label' => $m[1],
                'context' => trim($line)
            ];
        }
    }
}

echo "Total ->icon(...) calls found in app/Filament: " . count($actionIcons) . "\n";
echo "Total Action::make found: " . count($tableActions) . "\n";
echo "Emoji in Action labels found: " . count($buttonIssues) . "\n";

if (!empty($buttonIssues)) {
    echo "\n=== EMOJI IN ACTION LABELS ===\n";
    foreach ($buttonIssues as $issue) {
        echo "{$issue['file']}:{$issue['line']} -> Label: '{$issue['label']}'\n";
    }
}

file_put_contents(__DIR__ . '/action_icons_audit.json', json_encode([
    'action_icons' => $actionIcons,
    'button_issues' => $buttonIssues,
    'table_actions' => $tableActions
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
