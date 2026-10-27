<?php

namespace App\Services;

use App\Constants\FileConstants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileUploadService
{
    /**
     * Validate and upload file
     */
    public function upload(
        UploadedFile $file,
        string $directory,
        array $allowedMimeTypes = [],
        int $maxSize = 0
    ): string {
        // Validate file
        $this->validateFile($file, $allowedMimeTypes, $maxSize);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        // Store file
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(
        UploadedFile $file,
        array $allowedMimeTypes = [],
        int $maxSize = 0
    ): void {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('الملف غير صالح');
        }

        // Check file size
        $maxSizeKb = $maxSize ?: FileConstants::MAX_DOCUMENT_SIZE;
        if ($file->getSize() > ($maxSizeKb * 1024)) {
            throw new Exception("حجم الملف يجب أن لا يتجاوز " . ($maxSizeKb / 1024) . " ميجابايت");
        }

        // Check MIME type
        if (!empty($allowedMimeTypes)) {
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                throw new Exception('نوع الملف غير مسموح به');
            }
        }

        // Verify file extension matches MIME type
        $this->verifyExtension($file);

        // Scan for malware (if available)
        $this->scanForMalware($file);
    }

    /**
     * Generate secure filename
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $random = Str::random(16);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Verify file extension matches MIME type
     */
    protected function verifyExtension(UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();

        $validExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'video/mp4' => ['mp4'],
        ];

        if (isset($validExtensions[$mimeType])) {
            if (!in_array(strtolower($extension), $validExtensions[$mimeType])) {
                throw new Exception('امتداد الملف لا يتطابق مع نوعه');
            }
        }
    }

    /**
     * Scan file for malware (placeholder - requires ClamAV or similar)
     */
    protected function scanForMalware(UploadedFile $file): void
    {
        // TODO: Implement malware scanning using ClamAV or similar
        // For now, we'll do basic checks

        // Check for suspicious content in file
        $content = file_get_contents($file->getRealPath());

        // Check for common malware signatures
        $suspiciousPatterns = [
            '/<script.*?>.*?<\/script>/i',
            '/eval\(/i',
            '/base64_decode/i',
            '/exec\(/i',
            '/shell_exec/i',
            '/system\(/i',
            '/passthru/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new Exception('تم اكتشاف محتوى مشبوه في الملف');
            }
        }
    }

    /**
     * Upload image with validation
     */
    public function uploadImage(UploadedFile $file, string $directory): string
    {
        return $this->upload(
            $file,
            $directory,
            FileConstants::ALLOWED_IMAGE_TYPES,
            FileConstants::MAX_IMAGE_SIZE
        );
    }

    /**
     * Upload document with validation
     */
    public function uploadDocument(UploadedFile $file, string $directory): string
    {
        return $this->upload(
            $file,
            $directory,
            FileConstants::ALLOWED_DOCUMENT_TYPES,
            FileConstants::MAX_DOCUMENT_SIZE
        );
    }

    /**
     * Upload video with validation
     */
    public function uploadVideo(UploadedFile $file, string $directory): string
    {
        return $this->upload(
            $file,
            $directory,
            FileConstants::ALLOWED_VIDEO_TYPES,
            FileConstants::MAX_VIDEO_SIZE
        );
    }

    /**
     * Delete file
     */
    public function delete(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Get file URL
     */
    public function getUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Check if file exists
     */
    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}
