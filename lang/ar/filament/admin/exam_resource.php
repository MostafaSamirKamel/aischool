<?php

return [
    'navigation_label' => 'الامتحانات',
    'model_label' => 'امتحان',
    'plural_model_label' => 'الامتحانات',
    
    // Fields
    'course_field' => 'الكورس',
    'group_field' => 'المجموعة',
    'instructor_field' => 'المدرس',
    'title' => 'عنوان الامتحان',
    'description' => 'الوصف',
    'type' => 'نوع الامتحان',
    'type.quiz' => 'كويز',
    'type.midterm' => 'ميدتيرم',
    'type.final' => 'فاينال',
    'type.assignment' => 'تكليف',
    'type.project' => 'مشروع',
    'exam_date' => 'تاريخ الامتحان',
    'duration' => 'المدة (بالدقائق)',
    'total_marks' => 'الدرجة الكلية',
    'passing_marks' => 'درجة النجاح',
    'status' => 'الحالة',
    'status.scheduled' => 'مجدول',
    'status.in_progress' => 'جاري',
    'status.completed' => 'مكتمل',
    'status.cancelled' => 'ملغي',
    'instructions' => 'التعليمات',
    'location' => 'المكان',
    'syllabus' => 'المنهج',
    
    // Type Options
    'type_options' => [
        'quiz' => 'اختبار قصير',
        'midterm' => 'امتحان منتصف الفصل',
        'final' => 'امتحان نهائي',
        'assignment' => 'واجب',
        'project' => 'مشروع',
    ],
    
    // Status Options
    'status_options' => [
        'scheduled' => 'مجدول',
        'in_progress' => 'جاري',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
    ],
    
    // Sections
    'sections' => [
        'exam_info' => 'معلومات الامتحان',
        'exam_details' => 'تفاصيل الامتحان',
        'grading_info' => 'معلومات التقييم',
        'additional_info' => 'معلومات إضافية',
    ],
    
    // Table
    'course_name' => 'اسم الكورس',
    'group_name' => 'المجموعة',
    'instructor_name' => 'اسم المدرس',
    'exam_date_time' => 'تاريخ ووقت الامتحان',
    
    // Filters
    'filter_by_course' => 'تصفية حسب الكورس',
    'filter_by_group' => 'تصفية حسب المجموعة',
    'filter_by_instructor' => 'تصفية حسب المدرس',
    'filter_by_type' => 'تصفية حسب النوع',
    'filter_by_status' => 'تصفية حسب الحالة',
    'filter_by_date' => 'تصفية حسب التاريخ',
    
    // Helper Text
    'passing_percentage' => 'نسبة النجاح: :percentage%',
    'average_marks' => 'متوسط الدرجات: :average',
    'pass_rate' => 'نسبة النجاح: :rate%',

    // Form specific
    'tenant_id' => 'المؤسسة',
    'tenant_id_default' => 'اختر المؤسسة',
    'course_id' => 'الكورس',
    'group_id' => 'المجموعة',
    'instructor_id' => 'المدرس',

    // Table specific
    'tenant' => [
        'name' => 'المؤسسة',
    ],
    'course' => [
        'name' => 'الكورس',
    ],
    'group' => [
        'name' => 'المجموعة',
    ],
    'instructor' => [
        'full_name' => 'المدرس',
    ],
    'grades_count' => 'عدد الدرجات',
    'created_at' => 'تاريخ الإنشاء',
    'updated_at' => 'تاريخ التحديث',
];
