<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$assignment = \App\Models\Assignment::find(2);

if (!$assignment) {
    echo "❌ Assignment ID 2 not found\n";
    exit(1);
}

echo "✅ Assignment found: {$assignment->title}\n";
echo "   Attachment Path: " . ($assignment->attachment_path ?? 'null') . "\n";

if ($assignment->attachment_path) {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $exists = $disk->exists($assignment->attachment_path);
    
    echo "   File Path: " . $assignment->attachment_path . "\n";
    echo "   File Exists: " . ($exists ? 'YES' : 'NO') . "\n";
    echo "   Full Disk Path: " . storage_path('app/public/' . $assignment->attachment_path) . "\n";
    
    if (!$exists) {
        $fullPath = storage_path('app/public/' . $assignment->attachment_path);
        $actualPath = storage_path('app/public/');
        
        echo "\n❌ File not found at: $fullPath\n";
        echo "\n📁 Files in storage/app/public/:\n";
        
        if (is_dir($actualPath)) {
            $files = scandir($actualPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "   - $file\n";
                }
            }
        } else {
            echo "   ❌ Directory does not exist: $actualPath\n";
        }
    } else {
        echo "   ✅ File found successfully!\n";
        $size = $disk->size($assignment->attachment_path);
        echo "   File Size: " . number_format($size) . " bytes\n";
    }
} else {
    echo "   ❌ No attachment path set\n";
}

echo "\n📝 Storage Configuration:\n";
echo "   Default Disk: " . config('filesystems.default') . "\n";
echo "   Public Disk Root: " . storage_path('app/public') . "\n";
echo "   Public URL: " . env('APP_URL') . '/storage\n";

echo "\n🔗 Symlink Check:\n";
$symlinkPath = public_path('storage');
if (is_link($symlinkPath)) {
    echo "   ✅ Symlink exists: " . $symlinkPath . " -> " . readlink($symlinkPath) . "\n";
} else {
    echo "   ❌ Symlink does not exist at: $symlinkPath\n";
    echo "   📝 To create symlink, run: php artisan storage:link\n";
}
