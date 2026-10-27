<?php

return [
    'navigation_group' => 'الإدارة المالية',
    'navigation_label' => 'المصروفات',
    'model_label' => 'مصروف',
    'plural_model_label' => 'المصروفات',
    
    // Fields
    'expense_code' => 'كود المصروف',
    'expense_category_id' => 'فئة المصروف',
    'category' => 'الفئة',
    'category.name' => 'الفئة',
    'title' => 'العنوان',
    'description' => 'الوصف',
    'amount' => 'المبلغ',
    'expense_date' => 'تاريخ المصروف',
    'payment_method' => 'طريقة الدفع',
    'invoice_number' => 'رقم الفاتورة',
    'vendor_name' => 'اسم المورد',
    'notes' => 'ملاحظات',
    'status' => 'الحالة',
    'approved_by' => 'تمت الموافقة بواسطة',
    'approved_at' => 'تاريخ الموافقة',
    'created_by' => 'أنشئ بواسطة',
    'created_by.name' => 'أنشئ بواسطة',
    'created_at' => 'تاريخ الإنشاء',
    
    // Sections
    'basic_info' => 'المعلومات الأساسية',
    'financial_details' => 'التفاصيل المالية',
    'vendor_info' => 'معلومات المورد',
    'approval' => 'الاعتماد',
    
    // Payment Methods
    'payment_methods' => [
        'cash' => 'نقدي',
        'bank_transfer' => 'تحويل بنكي',
        'card' => 'بطاقة',
        'cheque' => 'شيك',
        'other' => 'أخرى',
    ],
    
    // Statuses
    'statuses' => [
        'pending' => 'قيد الانتظار',
        'approved' => 'معتمد',
        'paid' => 'مدفوع',
        'cancelled' => 'ملغي',
    ],
    
    // Other
    'auto_generated' => 'سيتم توليده تلقائياً',
    'total' => 'الإجمالي',
    'from_date' => 'من تاريخ',
    'to_date' => 'إلى تاريخ',
];
