<?php

return [
    // Bulk Grade Entry Page - Blade Template
    'bulk_grade_entry' => [
        'student_list_heading' => 'قائمة الطلاب (:count طالب)',
        'table' => [
            'number' => '#',
            'student_name' => 'اسم الطالب',
            'email' => 'البريد الإلكتروني',
            'marks_obtained' => 'الدرجة المستحقة',
            'remarks' => 'ملاحظات',
        ],
        'save_all' => 'حفظ جميع الدرجات',
        'no_students' => 'لا يوجد طلاب مسجلين في هذا الامتحان',
    ],

    // Student Transcript Page - Blade Template
    'transcript' => [
        'student_info' => [
            'heading' => 'معلومات الطالب',
            'full_name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'not_available' => 'غير متوفر',
        ],
        'statistics' => [
            'heading' => 'إحصائيات الأداء',
            'total_exams' => 'إجمالي الامتحانات',
            'average_percentage' => 'متوسط النسبة',
            'pass_fail' => 'نجح / رسب',
            'grade_distribution' => 'توزيع التقديرات',
            'highest_grade' => 'أعلى درجة',
            'lowest_grade' => 'أدنى درجة',
        ],
        'grades_by_course' => [
            'heading' => 'الدرجات حسب المقرر',
            'course' => 'المقرر',
            'exam' => 'الامتحان',
            'type' => 'النوع',
            'marks' => 'الدرجة',
            'percentage' => 'النسبة',
            'grade' => 'التقدير',
            'date' => 'التاريخ',
            'no_grades' => 'لا توجد درجات مسجلة',
        ],
        'actions' => [
            'download_pdf' => 'تحميل كشف الدرجات (PDF)',
            'print' => 'طباعة',
        ],
    ],

    // Issue Certificate Page - Blade Template
    'certificate' => [
        'preview_heading' => 'معاينة الشهادة',
        'certificate_info' => 'معلومات الشهادة',
        'student_name' => 'اسم الطالب',
        'course_name' => 'اسم الكورس',
        'issue_date' => 'تاريخ الإصدار',
        'generate_button' => 'إصدار الشهادة',
        'actions' => [
            'preview' => 'معاينة',
            'download' => 'تحميل',
            'print' => 'طباعة',
        ],
    ],

    // PDF Templates
    'pdf' => [
        'transcript' => [
            'title' => 'كشف درجات الطالب',
            'student_id' => 'الرقم الطلابي',
            'student_name' => 'اسم الطالب',
            'issue_date' => 'تاريخ الإصدار',
            'academic_record' => 'السجل الأكاديمي',
            'course' => 'المقرر',
            'exam' => 'الامتحان',
            'marks' => 'الدرجة',
            'percentage' => 'النسبة %',
            'grade' => 'التقدير',
            'overall_statistics' => 'الإحصائيات العامة',
            'total_courses' => 'إجمالي المقررات',
            'total_exams' => 'إجمالي الامتحانات',
            'average_percentage' => 'المتوسط العام',
            'signature' => 'التوقيع',
            'registrar' => 'مسجل الجامعة',
        ],
        'certificate' => [
            'title' => 'شهادة إتمام الكورس',
            'awarded_to' => 'تُمنح هذه الشهادة لـ',
            'completion_text' => 'لإتمامه بنجاح متطلبات',
            'course' => 'الكورس',
            'date' => 'التاريخ',
            'instructor' => 'المدرس',
            'director' => 'المدير',
            'seal' => 'الختم',
        ],
    ],

    // Exam Types (for displays in views)
    'exam_types' => [
        'quiz' => 'كويز',
        'midterm' => 'امتحان نصفي',
        'final' => 'امتحان نهائي',
        'assignment' => 'واجب',
        'project' => 'مشروع',
    ],

    // Grade Labels
    'grades' => [
        'A+' => 'ممتاز +',
        'A' => 'ممتاز',
        'B+' => 'جيد جداً +',
        'B' => 'جيد جداً',
        'C+' => 'جيد +',
        'C' => 'جيد',
        'D' => 'مقبول',
        'F' => 'راسب',
    ],

    // Status Labels
    'status' => [
        'pass' => 'ناجح',
        'fail' => 'راسب',
    ],
];