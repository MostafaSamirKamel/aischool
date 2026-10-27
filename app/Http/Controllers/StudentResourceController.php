<?php

namespace App\Http\Controllers;

use App\Models\LearningResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class StudentResourceController extends Controller
{
    /**
     * View a learning resource file
     */
    public function view(LearningResource $resource)
    {
        $user = auth()->user();
        if (!$user || !$user->student) {
            abort(403, 'لا يمكنك الوصول إلى هذا الملف. يجب أن تكون طالباً.');
        }

        // Increment view count
        $resource->increment('view_count');

        if ($resource->type === 'link') {
            return redirect()->away($resource->external_link);
        }

        if (!$resource->file_path) {
            abort(404, 'الملف غير متاح');
        }

        $filePaths = is_array($resource->file_path) ? $resource->file_path : [$resource->file_path];
        $firstFile = $filePaths[0] ?? null;

        if (!$firstFile) {
            abort(404, 'الملف غير متاح');
        }

        return $this->serveFile($firstFile, $resource->file_name ?? 'resource');
    }

    /**
     * Download a learning resource file
     */
    public function download(LearningResource $resource)
    {
        $user = auth()->user();
        if (!$user || !$user->student) {
            abort(403, 'لا يمكن تحميل هذا الملف');
        }

        if (!$resource->is_downloadable) {
            abort(403, 'لا يمكن تحميل هذا الملف');
        }

        if ($resource->type === 'link') {
            return redirect()->away($resource->external_link);
        }

        if (!$resource->file_path) {
            abort(404, 'الملف غير متاح');
        }

        $filePaths = is_array($resource->file_path) ? $resource->file_path : [$resource->file_path];
        $firstFile = $filePaths[0] ?? null;

        if (!$firstFile) {
            abort(404, 'الملف غير متاح');
        }

        // Increment download count
        $resource->increment('download_count');

        // Serve file as download
        $fileContent = Storage::disk('public')->get($firstFile);
        $mimeType = $this->getMimeType($firstFile);
        $fileName = $resource->file_name ?? basename($firstFile);

        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($fileContent),
        ]);
    }

    /**
     * Serve file with proper content type
     */
    private function serveFile(string $filePath, string $fileName = 'file'): Response
    {
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'الملف غير موجود');
        }

        $fileContent = Storage::disk('public')->get($filePath);
        $mimeType = $this->getMimeType($filePath);
        $fileName = $fileName ?: basename($filePath);

        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Get mime type for file
     */
    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
