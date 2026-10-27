<?php

namespace App\Constants;

class SystemConstants
{
    // Pagination
    public const DEFAULT_PER_PAGE = 15;
    public const MAX_PER_PAGE = 100;
    public const PAGINATION_OPTIONS = [15, 25, 50, 100];

    // SMS
    public const SMS_RATE_LIMIT_MINUTES = 1;
    public const SMS_MAX_PER_MINUTE = 5;
    public const SMS_MAX_LENGTH = 160;

    // Validation
    public const MIN_PASSWORD_LENGTH = 8;
    public const MAX_NAME_LENGTH = 255;
    public const MAX_DESCRIPTION_LENGTH = 1000;
    public const NATIONAL_ID_LENGTH = 14;

    // Grading
    public const MIN_GRADE = 0;
    public const MAX_GRADE = 100;
    public const PASSING_GRADE = 50;

    // Attendance
    public const LATE_THRESHOLD_MINUTES = 15;
    public const EXCUSED_ABSENCE_MAX_DAYS = 3;

    // Payment
    public const MIN_PAYMENT_AMOUNT = 1;
    public const MAX_PAYMENT_AMOUNT = 1000000;

    // Session
    public const SESSION_LIFETIME_MINUTES = 120;
    public const SESSION_IDLE_TIMEOUT_MINUTES = 30;

    // Cache
    public const CACHE_TTL_SHORT = 300; // 5 minutes
    public const CACHE_TTL_MEDIUM = 3600; // 1 hour
    public const CACHE_TTL_LONG = 86400; // 24 hours

    // Rate Limiting
    public const API_RATE_LIMIT_PER_MINUTE = 60;
    public const LOGIN_RATE_LIMIT_PER_MINUTE = 5;
    public const LOGIN_MAX_ATTEMPTS = 5;
    public const LOGIN_LOCKOUT_MINUTES = 15;
}
