<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Instructor;
use App\Models\Group;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Ensure course 78 exists or use first course
$course = Course::firstOrCreate(
    ['tenant_id' => 1, 'name' => 'Python Junior'],
    ['price' => 1500, 'sessions_count' => 8, 'status' => 'active']
);

$instUser = User::where('username', '01091812925')->first();
echo "Instructor User ID: {$instUser->id}" . PHP_EOL;

// Ensure Instructor has id = instUser->id if possible or update
$existingProfile = Instructor::where('user_id', $instUser->id)->first();
if ($existingProfile && $existingProfile->id !== $instUser->id) {
    echo "Updating Instructor ID from {$existingProfile->id} to {$instUser->id}..." . PHP_EOL;
    DB::table('instructors')->where('id', $existingProfile->id)->update(['id' => $instUser->id]);
}

$instProfile = Instructor::find($instUser->id);
echo "Instructor Profile ID: " . ($instProfile ? $instProfile->id : 'NOT FOUND') . PHP_EOL;

try {
    $group = Group::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'Group A — Python Junior'],
        [
            'course_id' => $course->id,
            'classroom_id' => 1,
            'instructor_id' => $instUser->id,
            'max_students' => 15,
            'capacity' => 15,
            'status' => 'active',
            'start_date' => Carbon::now()->subWeeks(3)->startOfWeek()->toDateString(),
            'end_date' => Carbon::now()->addWeeks(5)->endOfWeek()->toDateString(),
            'schedule' => [
                [
                    'day' => 'saturday',
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ]
            ],
        ]
    );

    echo "SUCCESS: Group created with ID: {$group->id}, Instructor: " . ($group->instructor?->user?->name ?? 'N/A') . PHP_EOL;
} catch (\Throwable $e) {
    echo "Group Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
