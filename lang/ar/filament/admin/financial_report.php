<?php

return [
    'title' => 'التقرير المالي',
    'navigation_label' => 'التقرير المالي',
    'heading' => 'تقرير تفصيلي للمدفوعات',
    
    'filters' => [
        'payment_method' => 'طريقة الدفع',
        'status' => 'الحالة',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'all_methods' => 'جميع الطرق',
        'all_statuses' => 'جميع الحالات',
        'apply' => 'تطبيق الفلاتر',
    ],
    
    'stats' => [
        'total_revenue' => 'إجمالي الإيرادات',
        'month_revenue' => 'إيرادات الشهر الحالي',
        'pending_payments' => 'مدفوعات معلقة',
        'refunded_amount' => 'المبالغ المستردة',
    ],
    
    'columns' => [
        'payment_code' => 'كود الدفع',
        'student' => 'الطالب',
        'course' => 'الدورة',
        'amount' => 'المبلغ',
        'payment_method' => 'طريقة الدفع',
        'status' => 'الحالة',
        'payment_date' => 'تاريخ الدفع',
        'created_at' => 'تاريخ الإنشاء',
        'total' => 'الإجمالي',
    ],
    
    'export' => 'تصدير Excel',
];
