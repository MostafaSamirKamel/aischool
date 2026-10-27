<?php

return [
    'navigation_label' => 'المجموعات',
    'model_label' => 'مجموعة',
    'plural_model_label' => 'المجموعات',
    
    // Fields
    'name' => 'الاسم',
    'capacity' => 'السعة',
    'max_students' => 'الحد الأقصى للطلاب',
    'start_date' => 'تاريخ البداية',
    'end_date' => 'تاريخ النهاية',
    'status' => 'الحالة',
    'completed_sessions' => 'الجلسات المكتملة',
    'enrollments_count' => 'عدد المسجلين',
    
    // Relations
    'course.name' => 'اسم الدورة',
    'classroom.name' => 'اسم الفصل',
    
    // Status
    'status.pending' => 'قيد الانتظار',
    'status.active' => 'نشط',
    'status.completed' => 'مكتمل',
    'status.cancelled' => 'ملغى',
];
