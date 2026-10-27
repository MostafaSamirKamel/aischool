<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default WhatsApp Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "sog", "ultramsg", "log"
    |
    */
    'default' => env('WHATSAPP_DEFAULT_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Queue WhatsApp Messages
    |--------------------------------------------------------------------------
    |
    | When enabled, WhatsApp messages will be queued for background processing
    |
    */
    'queue' => env('WHATSAPP_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Log Messages to Database
    |--------------------------------------------------------------------------
    |
    | When enabled, all WhatsApp messages will be logged to database
    |
    */
    'log_messages' => env('WHATSAPP_LOG', true),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Prevent sending too many messages in a short period
    |
    */
    'rate_limit' => [
        'per_minute' => env('WHATSAPP_RATE_LIMIT_PER_MINUTE', 5),
        'per_hour' => env('WHATSAPP_RATE_LIMIT_PER_HOUR', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | SOG (Egyptian Government) Configuration
    |--------------------------------------------------------------------------
    |
    | Get your credentials from: https://sender.whatsapp.sog.eg
    |
    */
    'sog' => [
        'api_url' => env('SOG_WHATSAPP_API_URL', 'https://sender.whatsapp.sog.eg/api'),
        'api_key' => env('SOG_WHATSAPP_API_KEY', ''),
        'sender' => env('SOG_WHATSAPP_SENDER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ultramsg Configuration (Alternative)
    |--------------------------------------------------------------------------
    |
    | Get your credentials from: https://ultramsg.com
    |
    */
    'ultramsg' => [
        'instance_id' => env('ULTRAMSG_INSTANCE_ID', ''),
        'token' => env('ULTRAMSG_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Message Templates
    |--------------------------------------------------------------------------
    |
    | Predefined message templates with variable placeholders
    |
    */
    'templates' => [
        'attendance_absent' => '🔴 *تحديث حضور*' . "\n\n" .
            'عزيزي ولي الأمر، نفيدكم بأن الطالب/ة *{student_name}* كان غائباً/ة في تاريخ {date}' . "\n\n" .
            'المجموعة: {group_name}' . "\n" .
            'الحصة: {session_title}' . "\n\n" .
            'للاستفسار يرجى التواصل مع الإدارة.',

        'attendance_late' => '⚠️ *إشعار تأخير*' . "\n\n" .
            'الطالب/ة *{student_name}* تأخر/ت عن الحصة في تاريخ {date}' . "\n\n" .
            'المجموعة: {group_name}' . "\n" .
            'وقت الوصول: {arrival_time}',

        'attendance_present' => '✅ *تأكيد حضور*' . "\n\n" .
            'تم تسجيل حضور الطالب/ة *{student_name}* في تاريخ {date}' . "\n\n" .
            'المجموعة: {group_name}' . "\n" .
            'الحصة: {session_title}',

        'new_grade' => '📝 *درجة جديدة*' . "\n\n" .
            'تم إضافة درجة جديدة للطالب/ة *{student_name}*' . "\n\n" .
            'الامتحان: {exam_title}' . "\n" .
            'الدرجة: {grade}/{max_grade}' . "\n" .
            'التقدير: {grade_letter}',

        'payment_received' => '💰 *تأكيد دفع*' . "\n\n" .
            'تم استلام دفعة بمبلغ *{amount} جنيه* بنجاح' . "\n\n" .
            'رقم الدفع: {payment_id}' . "\n" .
            'التاريخ: {date}' . "\n" .
            'المتبقي: {remaining} جنيه' . "\n\n" .
            'شكراً لثقتكم بنا! 🙏',

        'exam_reminder' => '📢 *تذكير امتحان*' . "\n\n" .
            'امتحان *{exam_title}* قريب!' . "\n\n" .
            'التاريخ: {exam_date}' . "\n" .
            'الوقت: {exam_time}' . "\n" .
            'المادة: {course_name}' . "\n\n" .
            'بالتوفيق لجميع الطلاب! 📚',

        'assignment_reminder' => '📋 *واجب جديد*' . "\n\n" .
            'تم إضافة واجب جديد: *{assignment_title}*' . "\n\n" .
            'الموعد النهائي: {due_date}' . "\n" .
            'المادة: {course_name}' . "\n" .
            'الدرجة: {max_grade}' . "\n\n" .
            'يرجى الالتزام بالموعد المحدد.',

        'group_message' => '📬 *رسالة من المجموعة*' . "\n\n" .
            '{message}' . "\n\n" .
            '👨‍🏫 {sender_name}' . "\n" .
            '{group_name}',

        'welcome' => '👋 *مرحباً بك!*' . "\n\n" .
            'أهلاً وسهلاً *{student_name}* في {school_name}!' . "\n\n" .
            'نتمنى لك رحلة تعليمية موفقة معنا 🎓' . "\n\n" .
            'في حالة وجود أي استفسار، لا تتردد في التواصل معنا.',

        'installment_reminder' => '⏰ *تذكير قسط*' . "\n\n" .
            'قسط مستحق للطالب/ة *{student_name}*' . "\n\n" .
            'المبلغ: {amount} جنيه' . "\n" .
            'موعد الاستحقاق: {due_date}' . "\n\n" .
            'يرجى السداد في أقرب وقت ممكن.',
    ],
];
