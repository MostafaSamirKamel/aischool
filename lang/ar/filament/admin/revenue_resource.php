<?php

return [
    'navigation_group' => 'الإدارة المالية',
    'navigation_label' => 'الإيرادات',
    'model_label' => 'إيراد',
    'plural_model_label' => 'الإيرادات',
    
    // Fields
    'revenue_code' => 'كود الإيراد',
    'revenue_category_id' => 'فئة الإيراد',
    'category' => 'الفئة',
    'category.name' => 'الفئة',
    'title' => 'العنوان',
    'description' => 'الوصف',
    'amount' => 'المبلغ',
    'revenue_date' => 'تاريخ الإيراد',
    'payment_method' => 'طريقة الدفع',
    'invoice_number' => 'رقم الفاتورة',
    'customer_name' => 'اسم العميل',
    'payment_id' => 'رقم الدفعة',
    'payment.id' => 'رقم الدفعة',
    'notes' => 'ملاحظات',
    'status' => 'الحالة',
    'received_by' => 'تم الاستلام بواسطة',
    'received_at' => 'تاريخ الاستلام',
    'created_by' => 'أنشئ بواسطة',
    'created_by.name' => 'أنشئ بواسطة',
    'created_at' => 'تاريخ الإنشاء',
    
    // Sections
    'basic_info' => 'المعلومات الأساسية',
    'financial_details' => 'التفاصيل المالية',
    'customer_info' => 'معلومات العميل',
    'status_section' => 'الحالة',
    
    // Payment Methods
    'payment_methods' => [
        'cash' => 'نقدي',
        'bank_transfer' => 'تحويل بنكي',
        'card' => 'بطاقة',
        'online' => 'أونلاين',
        'other' => 'أخرى',
    ],
    
    // Statuses
    'statuses' => [
        'pending' => 'قيد الانتظار',
        'received' => 'مستلم',
        'cancelled' => 'ملغي',
    ],
    
    // Other
    'auto_generated' => 'سيتم توليده تلقائياً',
    'linked_payment' => 'ربط مع دفعة طالب',
    'total' => 'الإجمالي',
    'from_date' => 'من تاريخ',
    'to_date' => 'إلى تاريخ',
];
