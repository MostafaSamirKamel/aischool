<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\DashboardRedirectController;
use App\Models\Exam;
use App\Models\ExamQuestion;

// Auto-redirect to appropriate dashboard based on user role
Route::get('/', DashboardRedirectController::class)->name('dashboard.redirect');

// Convenience redirects for Student PWA Portal
Route::redirect('/portal', '/student-portal/');
Route::redirect('/pwa', '/student-portal/');

// Dedicated lightweight edge health check endpoints
Route::get('/health', fn () => response('OK', 200, ['Content-Type' => 'text/plain']));
Route::get('/healthz', fn () => response('OK', 200, ['Content-Type' => 'text/plain']));

// Diagnostic & Emergency Migration Endpoint for Cloud / Wasmer deployments
Route::withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \App\Http\Middleware\CheckUserIsActive::class,
])->group(function () {
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
});

Route::post('/locale/switch', function () {
    $locale = request('locale', 'ar');

    if (in_array($locale, ['ar', 'en', 'fr'])) {
        Session::put('locale', $locale);
        App::setLocale($locale);
    }
    
    return back();
})->name('locale.switch');

// Helper function for exam access authorization
if (!function_exists('authorizeExamAccess')) {
    function authorizeExamAccess(?\App\Models\Exam $exam, ?\App\Models\User $user): bool
    {
        if (!$exam || !$user) {
            return false;
        }

        if ($user->is_super_admin ?? false) {
            return true;
        }

        // Enforce tenant isolation
        if ($user->tenant_id && $exam->tenant_id && (int) $user->tenant_id !== (int) $exam->tenant_id) {
            return false;
        }

        // Tenant Admin or Supervisor roles
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'administrator', 'supervisor', 'super_admin'])) {
            return true;
        }

        // Assigned instructor
        if ($user->instructor && $exam->instructor_id && (int) $user->instructor->id === (int) $exam->instructor_id) {
            return true;
        }

        // Student enrolled in exam group
        if ($user->student && $exam->group_id) {
            $isEnrolled = \App\Models\EnrollmentCourse::where('group_id', $exam->group_id)
                ->whereHas('enrollment', fn($q) => $q->where('student_id', $user->student->id)->where('status', 'active'))
                ->exists();
            if ($isEnrolled) {
                return true;
            }
        }

        // Parent of student enrolled in exam group
        if ($user->parent && $exam->group_id) {
            $childrenIds = $user->parent->students()->pluck('students.id');
            $isEnrolled = \App\Models\EnrollmentCourse::where('group_id', $exam->group_id)
                ->whereHas('enrollment', fn($q) => $q->whereIn('student_id', $childrenIds)->where('status', 'active'))
                ->exists();
            if ($isEnrolled) {
                return true;
            }
        }

        return false;
    }
}

// Route to serve exam PDF files with strict authorization
Route::get('/storage/exams/pdfs/{filename}', function ($filename) {
    if (!auth()->check()) {
        abort(401, 'Unauthorized');
    }

    $cleanFilename = basename($filename);
    $user = auth()->user();

    // Find the exam owning this PDF
    $exam = Exam::withoutGlobalScopes()->where('pdf_file', 'like', "%{$cleanFilename}")->first();
    if (!$exam) {
        $question = ExamQuestion::withoutGlobalScopes()->where('pdf_file', 'like', "%{$cleanFilename}")->first();
        if ($question) {
            $exam = Exam::withoutGlobalScopes()->find($question->exam_id);
        }
    }

    if (!$exam && !($user->is_super_admin ?? false)) {
        abort(404, 'Exam PDF not found');
    }

    if ($exam && !authorizeExamAccess($exam, $user)) {
        abort(403, 'Unauthorized access to exam PDF');
    }

    $possiblePaths = [
        storage_path('app/private/exams/pdfs/' . $cleanFilename),
        storage_path('app/exams/pdfs/' . $cleanFilename),
        $exam && $exam->pdf_file ? storage_path('app/' . ltrim($exam->pdf_file, '/')) : null,
        $exam && $exam->pdf_file ? storage_path('app/private/' . ltrim($exam->pdf_file, '/')) : null,
    ];

    $resolvedPath = null;
    foreach ($possiblePaths as $path) {
        if ($path && file_exists($path)) {
            $resolvedPath = $path;
            break;
        }
    }

    if (!$resolvedPath) {
        abort(404, 'PDF file not found on disk');
    }

    return response()->file($resolvedPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $cleanFilename . '"'
    ]);
})->name('exam.pdf');

// Route to serve exam images with authentication & record-level authorization
Route::get('/exams/{exam}/image/{index}', function ($examId, $index) {
    if (!auth()->check()) {
        abort(401, 'Unauthorized');
    }

    $exam = Exam::withoutGlobalScopes()->find($examId);
    if (!$exam) {
        abort(404, 'Exam not found');
    }

    $user = auth()->user();
    if (!authorizeExamAccess($exam, $user)) {
        abort(403, 'Unauthorized access to exam image');
    }

    if (!$exam->images || !isset($exam->images[$index])) {
        abort(404, 'Image not found');
    }

    $imagePath = $exam->images[$index];
    $path = storage_path('app/' . ltrim($imagePath, '/'));
    if (!file_exists($path)) {
        $path = storage_path('app/private/' . ltrim($imagePath, '/'));
    }

    if (!file_exists($path)) {
        abort(404, 'Image file not found on disk');
    }

    $mimeType = mime_content_type($path);

    return response()->file($path, [
        'Content-Type' => $mimeType,
    ]);
})->name('exam.image');

// Route to serve exam QUESTION PDF files
Route::get('/exams/questions/{question}/pdf', function ($questionId) {
    if (!auth()->check()) {
        abort(401, 'Unauthorized');
    }

    $question = ExamQuestion::withoutGlobalScopes()->with('exam.group')->find($questionId);
    if (!$question || !$question->exam) {
        abort(404, 'Question not found');
    }

    $exam = $question->exam;
    $user = auth()->user();
    if (!authorizeExamAccess($exam, $user)) {
        abort(403, 'Unauthorized access to question PDF');
    }

    if (empty($question->pdf_file)) {
        abort(404, 'No PDF attached to this question');
    }

    $path = storage_path('app/private/' . ltrim($question->pdf_file, '/'));
    if (!file_exists($path)) {
        $path = storage_path('app/' . ltrim($question->pdf_file, '/'));
    }

    if (!file_exists($path)) {
        abort(404, 'PDF file not found on disk');
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="question-' . $questionId . '.pdf"',
    ]);
})->name('exam.question.pdf');

// Route to serve exam QUESTION images
Route::get('/exams/questions/{question}/image/{index}', function ($questionId, $index) {
    if (!auth()->check()) {
        abort(401, 'Unauthorized');
    }

    $question = ExamQuestion::withoutGlobalScopes()->with('exam.group')->find($questionId);
    if (!$question || !$question->exam) {
        abort(404, 'Question not found');
    }

    $exam = $question->exam;
    $user = auth()->user();
    if (!authorizeExamAccess($exam, $user)) {
        abort(403, 'Unauthorized access to question image');
    }

    $images = $question->images ?? [];
    if (!isset($images[$index])) {
        abort(404, 'Image not found');
    }

    $imageRel = ltrim($images[$index], '/');
    $path = storage_path('app/' . $imageRel);
    if (!file_exists($path)) {
        $path = storage_path('app/private/' . $imageRel);
    }

    if (!file_exists($path)) {
        abort(404, 'Image file not found on disk');
    }

    $mimeType = mime_content_type($path);

    return response()->file($path, [
        'Content-Type' => $mimeType,
    ]);
})->name('exam.question.image');

// Route to serve homework submission images with record-level authorization
Route::get('/assignments/submissions/{submission}/image/{index?}', [\App\Http\Controllers\AssignmentSubmissionMediaController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('assignments.submissions.image');

// Routes for report exports
Route::middleware(['auth', 'verified'])->group(function () {
    // All Installments Report Exports
    Route::get('/admin/reports/all-installments/export-excel', function () {
        $controller = app(\App\Filament\Admin\Pages\Reports\AllInstallmentsReport::class);
        return $controller->exportToExcel();
    })->name('admin.reports.all-installments.export-excel');

    Route::get('/admin/reports/all-installments/export-pdf', function () {
        $controller = app(\App\Filament\Admin\Pages\Reports\AllInstallmentsReport::class);
        return $controller->exportToPdf();
    })->name('admin.reports.all-installments.export-pdf');

    // Installments Report Exports
    Route::get('/admin/reports/installments/export-excel', function () {
        $controller = app(\App\Filament\Admin\Pages\Reports\InstallmentsReport::class);
        return $controller->exportToExcel();
    })->name('admin.reports.installments.export-excel');

    Route::get('/admin/reports/installments/export-pdf', function () {
        $controller = app(\App\Filament\Admin\Pages\Reports\InstallmentsReport::class);
        return $controller->exportToPdf();
    })->name('admin.reports.installments.export-pdf');

    // Course Financial Report Exports
    Route::get('/admin/reports/course-financial/export-excel', function () {
        $controller = app(\App\Filament\Admin\Pages\Reports\CourseFinancialReport::class);
        return $controller->exportToExcel();
    })->name('admin.reports.course-financial.export-excel');

    Route::get('/admin/reports/course-financial/export-pdf', function () {
        $controller = app(\App\Filament\Admin\Pages\Reports\CourseFinancialReport::class);
        return $controller->exportToPdf();
    })->name('admin.reports.course-financial.export-pdf');

    // Vault Report Exports
    Route::get('/admin/reports/vault/export-excel', function () {
        $controller = app(\App\Filament\Admin\Pages\VaultReport::class);
        return $controller->exportToExcel();
    })->name('admin.reports.vault.export-excel');

    Route::get('/admin/reports/vault/export-pdf', function () {
        $controller = app(\App\Filament\Admin\Pages\VaultReport::class);
        return $controller->exportToPdf();
    })->name('admin.reports.vault.export-pdf');

    // Student Learning Resources Routes
    Route::get('/student/resources/{resource}/view', [\App\Http\Controllers\StudentResourceController::class, 'view'])
        ->name('student.resources.view')
        ->middleware(['auth']);

    Route::get('/student/resources/{resource}/download', [\App\Http\Controllers\StudentResourceController::class, 'download'])
        ->name('student.resources.download')
        ->middleware(['auth']);

    // Student Assignment Attachment Routes
    Route::get('/student/assignments/{id}/view', function ($id) {
        $assignment = \App\Models\Assignment::find($id);
        
        if (!$assignment) {
            abort(404, 'الواجب غير موجود.');
        }

        $student = auth()->user()->student;

        // Verify student is enrolled in the assignment's group
        $hasAccess = \App\Models\Enrollment::where('student_id', $student?->id)
            ->where('status', 'active')
            ->with('enrollmentCourses')
            ->get()
            ->flatMap(fn($e) => $e->enrollmentCourses->pluck('group_id'))
            ->contains($assignment->group_id);

        if (!$hasAccess) {
            abort(403, 'ليس لديك صلاحية الوصول لهذا الواجب.');
        }

        if (!$assignment->attachment_path) {
            abort(404, 'لا يوجد ملف مرفق بهذا الواجب.');
        }

        $filePath = $assignment->attachment_path;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (!$disk->exists($filePath)) {
            abort(404, 'الملف غير موجود على السيرفر: ' . $filePath);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'txt'  => 'text/plain',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $mime = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response($disk->get($filePath), 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    })
    ->name('student.assignment.view')
    ->middleware(['auth', 'verified'])
    ->where('id', '[0-9]+');

    Route::get('/student/assignments/{id}/download', function ($id) {
        $assignment = \App\Models\Assignment::find($id);
        
        if (!$assignment) {
            abort(404, 'الواجب غير موجود.');
        }

        $student = auth()->user()->student;

        $hasAccess = \App\Models\Enrollment::where('student_id', $student?->id)
            ->where('status', 'active')
            ->with('enrollmentCourses')
            ->get()
            ->flatMap(fn($e) => $e->enrollmentCourses->pluck('group_id'))
            ->contains($assignment->group_id);

        if (!$hasAccess) {
            abort(403, 'ليس لديك صلاحية الوصول لهذا الواجب.');
        }

        if (!$assignment->attachment_path) {
            abort(404, 'لا يوجد ملف مرفق بهذا الواجب.');
        }

        $filePath = $assignment->attachment_path;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (!$disk->exists($filePath)) {
            abort(404, 'الملف غير موجود على السيرفر: ' . $filePath);
        }

        $fileName = basename($filePath);

        return response($disk->get($filePath), 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    })
    ->name('student.assignment.download')
    ->middleware(['auth', 'verified'])
    ->where('id', '[0-9]+');
});
