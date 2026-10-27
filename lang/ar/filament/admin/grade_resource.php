<?php

return [
    'navigation_label' => 'النتائج الرسمية',
    'model_label' => 'نتيجة رسمية',
    'plural_model_label' => 'النتائج الرسمية',
    
    // Fields
    'exam_field' => 'الامتحان',
    'student_field' => 'الطالب',
    'marks_obtained' => 'الدرجة المحصلة',
    'percentage' => 'النسبة المئوية',
    'grade_field' => 'التقدير',
    'grade_points' => 'نقاط التقدير',
    'remarks' => 'ملاحظات',
    'graded_by' => 'قيم بواسطة',
    'graded_at' => 'تاريخ التقييم',
    'status' => 'الحالة',
    
    // Grade Options
    'grade_options' => [
        'A+' => 'ممتاز +',
        'A' => 'ممتاز',
        'B+' => 'جيد جداً +',
        'B' => 'جيد جداً',
        'C+' => 'جيد +',
        'C' => 'جيد',
        'D' => 'مقبول',
        'F' => 'راسب',
    ],
    
    // Status Options
    'status_options' => [
        'pass' => 'ناجح',
        'fail' => 'راسب',
    ],
    
    // Sections
    'sections' => [
        'grade_info' => 'معلومات الدرجة',
        'student_info' => 'معلومات الطالب',
        'grading_details' => 'تفاصيل التقييم',
        'additional_info' => 'معلومات إضافية',
    ],
    
    // Table
    'exam_title' => 'عنوان الامتحان',
    'student_name' => 'اسم الطالب',
    'marks' => 'الدرجة',
    'grade_label' => 'التقدير',
    'graded_by_name' => 'المقيم',
    
    // Filters
    'filter_by_exam' => 'تصفية حسب الامتحان',
    'filter_by_student' => 'تصفية حسب الطالب',
    'filter_by_grade' => 'تصفية حسب التقدير',
    'filter_by_status' => 'تصفية حسب الحالة',
    
    // Helper Text
    'auto_calculated' => 'محسوبة تلقائياً',
    'total_marks' => 'الدرجة الكلية: :total',
    'passing_marks' => 'درجة النجاح: :passing',
    'grade_label_text' => ':grade (:points نقطة)',
    
    // Messages
    'grade_calculated' => 'تم حساب التقدير تلقائياً',
    'percentage_calculated' => 'تم حساب النسبة المئوية تلقائياً',

    // Form specific
    'tenant_id' => 'المؤسسة',
    'tenant_id_default' => 'اختر المؤسسة',
    'exam_id' => 'الامتحان',
    'student_id' => 'الطالب',
    'enrollment_id' => 'التسجيل',

    // Grade options for form
    'grade' => [
        'a+' => 'ممتاز +',
        'a' => 'ممتاز',
        'b+' => 'جيد جداً +',
        'b' => 'جيد جداً',
        'c+' => 'جيد +',
        'c' => 'جيد',
        'd' => 'مقبول',
        'f' => 'راسب',
    ],

    // Table specific
    'tenant' => [
        'name' => 'المؤسسة',
    ],
    'student' => [
        'full_name' => 'الطالب',
    ],
    'exam' => [
        'title' => 'الامتحان',
        'type' => 'نوع الامتحان',
    ],
    'graded_by_user' => [
        'name' => 'المقيم',
    ],
    'created_at' => 'تاريخ الإنشاء',
    'updated_at' => 'تاريخ التحديث',
];
