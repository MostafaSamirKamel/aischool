<?php

return [
    'title' => 'تقرير الأداء',
    'navigation_label' => 'تقرير الأداء',
    'heading' => 'تقرير أداء الدورات والمجموعات',
    
    'filters' => [
        'course' => 'الدورة',
        'status' => 'حالة المجموعة',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'all_courses' => 'جميع الدورات',
        'all_statuses' => 'جميع الحالات',
        'apply' => 'تطبيق الفلاتر',
    ],
    
    'stats' => [
        'total_groups' => 'إجمالي المجموعات',
        'active_groups' => 'المجموعات النشطة',
        'avg_occupancy' => 'متوسط الإشغال',
        'total_revenue' => 'إجمالي الإيرادات',
    ],
    
    'columns' => [
        'course' => 'الدورة',
        'group' => 'المجموعة',
        'instructor' => 'المدرس',
        'students_count' => 'عدد الطلاب',
        'capacity' => 'السعة',
        'occupancy_rate' => 'نسبة الإشغال',
        'revenue' => 'الإيرادات',
        'status' => 'الحالة',
        'start_date' => 'تاريخ البدء',
        'end_date' => 'تاريخ الانتهاء',
        'total' => 'الإجمالي',
    ],
    
    'export' => 'تصدير Excel',
];
