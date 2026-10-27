<?php
$files = glob('app/Providers/Filament/*.php');
foreach($files as $file) {
    $content = file_get_contents($file);
    $bad = "fn ()\n            ->renderHook(";
    $bad2 = "fn ()\r\n            ->renderHook(";
    $good = "fn (): string => '<link rel=\"stylesheet\" href=\"' . asset('css/custom-theme.css') . '?v=' . (file_exists(public_path('css/custom-theme.css')) ? filemtime(public_path('css/custom-theme.css')) : time()) . '\">',\n            )\n            ->renderHook(";
    
    $content = str_replace($bad, $good, $content);
    $content = str_replace($bad2, $good, $content);
    
    file_put_contents($file, $content);
}
