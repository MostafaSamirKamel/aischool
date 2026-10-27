<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "آخر 5 مجموعات:\n";
$groups = \App\Models\Group::latest()->take(5)->get();
foreach ($groups as $group) {
    echo "- {$group->name} (ID: {$group->id}, Instructor: {$group->instructor_id})\n";
}
