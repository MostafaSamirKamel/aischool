<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StudentController as V1StudentController;

// Legacy API Routes (deprecated - will be removed)
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes V1 (New - Swagger Documented)
|--------------------------------------------------------------------------
*/

// Authentication routes (public)
Route::prefix('v1')->middleware(['api.rate.limit'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes
Route::prefix('v1')->middleware(['auth:sanctum', 'api.rate.limit'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Students
    Route::apiResource('students', V1StudentController::class);
    Route::get('students/{student}/enrollments', [V1StudentController::class, 'enrollments']);
    Route::get('students/{student}/attendance', [V1StudentController::class, 'attendance']);

    // Student Mobile / PWA Portal Endpoints (v1)
    Route::prefix('student')->group(function () {
        Route::get('/portal', [\App\Http\Controllers\Api\StudentPortalController::class, 'index']);
        Route::get('/exams/{id}', [\App\Http\Controllers\Api\StudentPortalController::class, 'getExam']);
        Route::post('/exams/{id}/start', [\App\Http\Controllers\Api\StudentPortalController::class, 'startExam']);
        Route::post('/exams/{id}/submit', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitExam']);
        Route::post('/assignments/{id}/submit', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitAssignment']);
        Route::post('/reviews', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitReview']);
        Route::get('/projects', [\App\Http\Controllers\Api\StudentPortalController::class, 'getProjects']);
        Route::post('/projects', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitProject']);
        Route::get('/timeline', [\App\Http\Controllers\Api\StudentPortalController::class, 'getTimeline']);
    });
});

// Root student portal endpoints for Student PWA
Route::middleware(['auth:sanctum', 'api.rate.limit'])->prefix('student')->group(function () {
    Route::get('/portal', [\App\Http\Controllers\Api\StudentPortalController::class, 'index']);
    Route::get('/exams/{id}', [\App\Http\Controllers\Api\StudentPortalController::class, 'getExam']);
    Route::post('/exams/{id}/start', [\App\Http\Controllers\Api\StudentPortalController::class, 'startExam']);
    Route::post('/exams/{id}/submit', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitExam']);
    Route::post('/assignments/{id}/submit', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitAssignment']);
    Route::post('/reviews', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitReview']);
    Route::get('/projects', [\App\Http\Controllers\Api\StudentPortalController::class, 'getProjects']);
    Route::post('/projects', [\App\Http\Controllers\Api\StudentPortalController::class, 'submitProject']);
    Route::get('/timeline', [\App\Http\Controllers\Api\StudentPortalController::class, 'getTimeline']);
});

/*
|--------------------------------------------------------------------------
| Legacy API Routes (Deprecated)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'api.rate.limit'])->group(function () {
    
    // Student endpoints
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::get('/{id}/grades', [StudentController::class, 'grades']);
        Route::get('/{id}/attendance', [StudentController::class, 'attendance']);
        Route::get('/{id}/transcript', [StudentController::class, 'transcript']);
    });

    // Exam endpoints
    Route::prefix('exams')->group(function () {
        Route::get('/upcoming', [ExamController::class, 'upcoming']);
        Route::get('/', [ExamController::class, 'index']);
        Route::get('/{id}', [ExamController::class, 'show']);
    });

    // Grade endpoints
    Route::prefix('grades')->group(function () {
        Route::get('/', [GradeController::class, 'index']);
        Route::get('/{id}', [GradeController::class, 'show']);
        Route::post('/', [GradeController::class, 'store']);
    });

    // Attendance endpoints
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/', [AttendanceController::class, 'store']);
    });
});

// Diagnostic & Cloud migration endpoints
Route::get('/diag', function () {
    $results = [];
    $results['app_key_set'] = !empty(config('app.key'));
    $results['session_driver'] = config('session.driver');
    $results['db_connection'] = config('database.default');
    
    try {
        $db = config('database.connections.' . config('database.default'));
        $results['db_config'] = [
            'host' => $db['host'] ?? null,
            'port' => $db['port'] ?? null,
            'database' => $db['database'] ?? null,
            'username' => $db['username'] ?? null,
        ];
        
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $results['db_connected'] = true;
        
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $results['tables_count'] = count($tables);
        $results['has_users'] = \Illuminate\Support\Facades\Schema::hasTable('users');
        $results['has_sessions'] = \Illuminate\Support\Facades\Schema::hasTable('sessions');
        if ($results['has_users']) {
            $results['users_count'] = \App\Models\User::count();
        }
    } catch (\Throwable $e) {
        $results['db_connected'] = false;
        $results['db_error'] = $e->getMessage();
    }
    
    return response()->json($results);
});

Route::get('/diag/migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Illuminate\Support\Facades\Artisan::output();
        
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TenantSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoUserSeeder', '--force' => true]);
        $seedOutput = \Illuminate\Support\Facades\Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
            'users_count' => \App\Models\User::count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
        ], 500);
    }
});
