<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssignmentSubmissionMediaController extends Controller
{
    /**
     * Authorize and serve a submitted homework image securely.
     */
    public function show(Request $request, AssignmentSubmission $submission, int $index = 0): BinaryFileResponse
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'يجب تسجيل الدخول للوصول إلى هذا الملف.');
        }

        // 1. Enforce multi-tenant isolation
        if (!$user->is_super_admin && !($user->hasRole('super_admin') ?? false)) {
            if ($user->tenant_id && $submission->tenant_id && (int) $user->tenant_id !== (int) $submission->tenant_id) {
                abort(403, 'غير مصرح لك بالوصول إلى بيانات فرع آخر.');
            }
        }

        // 2. Role-based authorization
        $isAuthorized = false;

        // Super Admin or Branch Admin or Supervisor
        if ($user->is_super_admin || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'administrator', 'branch_manager', 'supervisor']))) {
            $isAuthorized = true;
        }

        // Student owner
        if (!$isAuthorized && $user->student && (int) $user->student->id === (int) $submission->student_id) {
            $isAuthorized = true;
        }

        // Parent of the student
        if (!$isAuthorized && method_exists($user, 'hasRole') && $user->hasRole('parent')) {
            $childrenIds = $user->students()->pluck('students.id')->toArray();
            if (in_array((int) $submission->student_id, $childrenIds)) {
                $isAuthorized = true;
            }
        }

        // Assigned Instructor
        if (!$isAuthorized && $user->instructor && $submission->assignment) {
            $assignment = $submission->assignment;
            $instructorId = (int) $user->instructor->id;
            if ((int) $assignment->instructor_id === $instructorId || (int) $assignment->group?->instructor_id === $instructorId) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'غير مصرح لك بعرض صور هذا الواجب.');
        }

        // 3. Retrieve the image path
        $images = $submission->images_list;
        if (!isset($images[$index])) {
            abort(404, 'الصورة المطلوبة غير موجودة.');
        }

        $imagePath = $images[$index];

        // 4. Resolve file path safely using Storage disk, with fallback
        $resolvedPath = null;
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($imagePath)) {
            $resolvedPath = \Illuminate\Support\Facades\Storage::disk('local')->path($imagePath);
        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
            $resolvedPath = \Illuminate\Support\Facades\Storage::disk('public')->path($imagePath);
        } else {
            $possiblePaths = [
                storage_path('app/private/' . ltrim($imagePath, '/')),
                storage_path('app/' . ltrim($imagePath, '/')),
                storage_path('app/public/' . ltrim($imagePath, '/')),
                public_path('storage/' . ltrim($imagePath, '/')),
            ];

            foreach ($possiblePaths as $p) {
                if (file_exists($p) && is_file($p)) {
                    $resolvedPath = realpath($p);
                    break;
                }
            }
        }

        if (!$resolvedPath || !file_exists($resolvedPath)) {
            abort(404, 'ملف الصورة غير موجود على الخادم.');
        }

        // Ensure path is within the storage or public application directory
        $normalizedResolved = str_replace('\\', '/', $resolvedPath);
        $normalizedStorage = str_replace('\\', '/', realpath(storage_path()) ?: storage_path());
        $normalizedPublic = str_replace('\\', '/', realpath(public_path()) ?: public_path());

        if (!str_starts_with($normalizedResolved, $normalizedStorage) && !str_starts_with($normalizedResolved, $normalizedPublic)) {
            abort(403, 'مسار الملف غير مسموح به.');
        }

        $mimeType = @mime_content_type($resolvedPath) ?: 'image/jpeg';

        return response()->file($resolvedPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
