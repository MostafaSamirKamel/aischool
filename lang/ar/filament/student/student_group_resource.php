<?php

return [
    'navigation_label' => 'المجموعات',
    'model_label' => 'مجموعة',
    'plural_model_label' => 'المجموعات',
    
    // Fields
    'tenant_id' => 'المنظمة',
    'course_id' => 'الدورة',
    'classroom_id' => 'الفصل',
    'instructor_id' => 'المدرس',
    'supervisor_id' => 'المشرف',
    'name' => 'الاسم',
    'capacity' => 'السعة',
    'max_students' => 'الحد الأقصى للطلاب',
    'schedule' => 'الجدول',
    'start_date' => 'تاريخ البداية',
    'end_date' => 'تاريخ النهاية',
    'status' => 'الحالة',
    'notes' => 'ملاحظات',
    
    // Relations
    'course.name' => 'اسم الدورة',
    'instructor.name' => 'اسم المدرس',
    'classroom.name' => 'اسم الفصل',
    
    // Status
    'status.pending' => 'قيد الانتظار',
    'status.active' => 'نشط',
    'status.completed' => 'مكتمل',
    'status.cancelled' => 'ملغى',
];
