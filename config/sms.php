<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS driver that will be used to send
    | SMS messages. You may set this to any of the drivers defined in the
    | "drivers" configuration array below.
    |
    | Supported: "twilio", "nexmo", "vonage", "vodafone", "log"
    |
    */

    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Drivers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the SMS drivers used by your application.
    | Each driver has its own configuration settings.
    |
    */

    'drivers' => [

        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

        'nexmo' => [
            'key' => env('NEXMO_KEY'),
            'secret' => env('NEXMO_SECRET'),
            'from' => env('NEXMO_FROM'),
        ],

        'vonage' => [
            'key' => env('VONAGE_KEY'),
            'secret' => env('VONAGE_SECRET'),
            'from' => env('VONAGE_FROM'),
        ],

        'vodafone' => [
            'username' => env('VODAFONE_USERNAME'),
            'password' => env('VODAFONE_PASSWORD'),
            'sender_id' => env('VODAFONE_SENDER_ID'),
            'account' => env('VODAFONE_ACCOUNT'),
            'secure' => env('VODAFONE_SECURE', 'true'),
        ],

        'log' => [
            'channel' => env('SMS_LOG_CHANNEL', 'single'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Logging
    |--------------------------------------------------------------------------
    |
    | This option determines whether all SMS messages should be logged to
    | the database for tracking and auditing purposes.
    |
    */

    'log_messages' => env('SMS_LOG_MESSAGES', true),

    /*
    |--------------------------------------------------------------------------
    | SMS Queue
    |--------------------------------------------------------------------------
    |
    | This option determines whether SMS messages should be queued for
    | background processing to improve application performance.
    |
    */

    'queue' => env('SMS_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | SMS Rate Limiting
    |--------------------------------------------------------------------------
    |
    | These options help prevent SMS flooding by limiting the number of
    | messages that can be sent per minute/hour.
    |
    */

    'rate_limit' => [
        'enabled' => env('SMS_RATE_LIMIT_ENABLED', true),
        'max_per_minute' => env('SMS_RATE_LIMIT_PER_MINUTE', 10),
        'max_per_hour' => env('SMS_RATE_LIMIT_PER_HOUR', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    |
    | Define reusable SMS templates with placeholders
    |
    */

    'templates' => [
        'attendance_absent' => 'عزيزي ولي الأمر، نفيدكم بأن الطالب/ة {student_name} كان غائباً/ة اليوم {date}. {school_name}',
        'attendance_late' => 'عزيزي ولي الأمر، نفيدكم بأن الطالب/ة {student_name} تأخر/ت اليوم {date}. {school_name}',
        'exam_reminder' => 'تذكير: لدى الطالب/ة {student_name} امتحان {exam_title} في {exam_date}. {school_name}',
        'payment_received' => 'تم استلام دفعة بقيمة {amount} جنيه للطالب/ة {student_name}. شكراً لكم. {school_name}',
        'new_grade' => 'درجة الطالب/ة {student_name} في {exam_title}: {score}/{total}. {school_name}',
    ],

    /*
    |--------------------------------------------------------------------------
    | School Information
    |--------------------------------------------------------------------------
    |
    | Information used in SMS templates
    |
    */

    'school_name' => env('SCHOOL_NAME', 'مدرستي'),
    'school_phone' => env('SCHOOL_PHONE', ''),

];
