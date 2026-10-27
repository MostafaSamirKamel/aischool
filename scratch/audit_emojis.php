<?php

$dirs = [
    __DIR__ . '/../resources/views',
    __DIR__ . '/../app/Filament'
];

$emojiMatches = [];

foreach ($dirs as $scanDir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanDir));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $ext = $file->getExtension();
        if (!in_array($ext, ['php', 'blade.php'])) continue;

        $content = file_get_contents($file->getRealPath());
        $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $file->getRealPath());
        
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            // Match common emoji ranges
            if (preg_match_all('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $line, $m)) {
                // Ignore comments if possible, but keep track
                $emojiMatches[] = [
                    'file' => $rel,
                    'line' => $lineNum + 1,
                    'emojis' => implode('', $m[0]),
                    'snippet' => trim($line)
                ];
            }
        }
    }
}

echo "Total Emoji occurrences in code/views: " . count($emojiMatches) . "\n\n";
foreach ($emojiMatches as $e) {
    echo "{$e['file']}:{$e['line']} [{$e['emojis']}] -> {$e['snippet']}\n";
}

file_put_contents(__DIR__ . '/emoji_audit.json', json_encode($emojiMatches, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
