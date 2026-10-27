<?php

// تغيير namespace من Admin إلى Owner في جميع الملفات المنسوخة

$directories = [
    __DIR__ . '/app/Filament/Owner/Resources/Students',
    __DIR__ . '/app/Filament/Owner/Resources/Courses',
    __DIR__ . '/app/Filament/Owner/Resources/Groups',
    __DIR__ . '/app/Filament/Owner/Resources/Enrollments',
    __DIR__ . '/app/Filament/Owner/Resources/Tracks',
    __DIR__ . '/app/Filament/Owner/Resources/Classrooms',
];

$filesUpdated = 0;

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        echo "❌ Directory not found: $directory\n";
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $originalContent = $content;
            
            // تغيير namespace
            $content = str_replace(
                'namespace App\Filament\Admin',
                'namespace App\Filament\Owner',
                $content
            );
            
            // تغيير use statements
            $content = str_replace(
                'use App\Filament\Admin',
                'use App\Filament\Owner',
                $content
            );
            
            if ($content !== $originalContent) {
                file_put_contents($file->getPathname(), $content);
                $filesUpdated++;
                echo "✅ Updated: " . $file->getFilename() . "\n";
            }
        }
    }
}

echo "\n✅ Total files updated: $filesUpdated\n";
