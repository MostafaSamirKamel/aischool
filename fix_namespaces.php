<?php

// تغيير namespace من Branch إلى Admin في جميع الملفات المنسوخة

$directories = [
    __DIR__ . '/app/Filament/Admin/Resources/Students',
    __DIR__ . '/app/Filament/Admin/Resources/Courses',
    __DIR__ . '/app/Filament/Admin/Resources/Groups',
    __DIR__ . '/app/Filament/Admin/Resources/Enrollments',
    __DIR__ . '/app/Filament/Admin/Resources/Tracks',
    __DIR__ . '/app/Filament/Admin/Resources/Classrooms',
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
                'namespace App\Filament\Branch',
                'namespace App\Filament\Admin',
                $content
            );
            
            // تغيير use statements
            $content = str_replace(
                'use App\Filament\Branch',
                'use App\Filament\Admin',
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