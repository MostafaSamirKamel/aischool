<?php

$dir = __DIR__ . '/../app/Filament';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$tableFiles = [];
$actionsWithoutIcons = [];

foreach ($it as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.php')) continue;
    $filePath = $file->getRealPath();
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $filePath);
    $content = file_get_contents($filePath);
    
    // Look for Actions::make([ ... ]) or ->actions([ ... ])
    if (preg_match_all('/(ActionGroup::make|EditAction::make|ViewAction::make|DeleteAction::make|ForceDeleteAction::make|RestoreAction::make|Action::make\s*\(\s*[\'"][^\'"]+[\'"]\s*\))([^;]+)/s', $content, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[0] as $match) {
            $snippet = $match[0];
            $offset = $match[1];
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;
            
            // Check if this action block has an ->icon(...)
            $hasIcon = str_contains($snippet, '->icon(');
            $actionType = trim(explode('(', $snippet)[0]);
            
            // For custom Action::make, check if it has icon
            if (str_starts_with($actionType, 'Action::make') || str_starts_with($actionType, 'Tables\Actions\Action::make')) {
                preg_match('/Action::make\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $snippet, $nameM);
                $actionName = $nameM[1] ?? 'unknown';
                
                // Truncate snippet to just this action definition (before next Action::make or ->actions)
                $firstActionSnippet = explode('Action::make', $snippet)[0] ?? $snippet;
                
                if (!$hasIcon) {
                    $actionsWithoutIcons[] = [
                        'file' => $rel,
                        'line' => $line,
                        'action' => $actionName,
                        'snippet' => substr($snippet, 0, 100)
                    ];
                }
            }
        }
    }
}

echo "Custom Actions without explicit icon: " . count($actionsWithoutIcons) . "\n";
foreach (array_slice($actionsWithoutIcons, 0, 30) as $a) {
    echo "{$a['file']}:{$a['line']} -> Action '{$a['action']}' has no explicit ->icon(...)\n";
}

file_put_contents(__DIR__ . '/table_actions_audit.json', json_encode($actionsWithoutIcons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
