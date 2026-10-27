<?php

namespace App\Constants;

class FileConstants
{
    // File Upload Sizes (in KB)
    public const MAX_IMAGE_SIZE = 2048; // 2MB
    public const MAX_DOCUMENT_SIZE = 10240; // 10MB
    public const MAX_LEARNING_RESOURCE_SIZE = 51200; // 50MB
    public const MAX_VIDEO_SIZE = 102400; // 100MB

    // Allowed MIME Types
    public const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public const ALLOWED_DOCUMENT_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    public const ALLOWED_VIDEO_TYPES = [
        'video/mp4',
        'video/mpeg',
        'video/quicktime',
        'video/x-msvideo',
    ];

    // File Upload Directories
    public const STUDENT_IMAGES_DIR = 'students/images';
    public const INSTRUCTOR_IMAGES_DIR = 'instructors/images';
    public const LEARNING_RESOURCES_DIR = 'learning-resources';
    public const ASSIGNMENTS_DIR = 'assignments';
    public const SUBMISSIONS_DIR = 'submissions';
    public const CERTIFICATES_DIR = 'certificates';
}
