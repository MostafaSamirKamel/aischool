<?php
$dir = __DIR__ . '/../';

function scanDirForEmojis($path, &$results) {
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === 'vendor' || $item === 'node_modules' || $item === '.git' || $item === 'storage') continue;
        $fullPath = $path . '/' . $item;
        if (is_dir($fullPath)) {
            scanDirForEmojis($fullPath, $results);
        } elseif (preg_match('/\.(php|blade\.php)$/', $item)) {
            $content = file_get_contents($fullPath);
            // Match emoji characters
            if (preg_match_all('/[\x{1F300}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1F1E0}-\x{1F1FF}]/u', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $fileEmojis = [];
                foreach ($matches[0] as $m) {
                    $char = $m[0];
                    $offset = $m[1];
                    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                    $fileEmojis[] = ['char' => $char, 'line' => $line];
                }
                $results[str_replace('\\', '/', $fullPath)] = $fileEmojis;
            }
        }
    }
}

$results = [];
scanDirForEmojis($dir . 'app', $results);
scanDirForEmojis($dir . 'resources/views', $results);

echo "Found " . count($results) . " files with emojis:\n\n";
foreach ($results as $file => $emojis) {
    $short = str_replace(str_replace('\\', '/', realpath($dir)) . '/', '', $file);
    $chars = array_unique(array_column($emojis, 'char'));
    echo "- {$short} (" . count($emojis) . " emojis: " . implode(' ', $chars) . ")\n";
}
