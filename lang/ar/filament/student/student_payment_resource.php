<?php

return [
    'navigation_label' => 'المدفوعات',
    'model_label' => 'دفعة',
    'plural_model_label' => 'المدفوعات',
    
    // Fields
    'tenant_id' => 'المنظمة',
    'enrollment_id' => 'التسجيل',
    'payment_code' => 'رمز الدفع',
    'amount' => 'المبلغ',
    'payment_method' => 'طريقة الدفع',
    'payment_date' => 'تاريخ الدفع',
    'due_date' => 'تاريخ الاستحقاق',
    'status' => 'الحالة',
    'transaction_reference' => 'مرجع المعاملة',
    'notes' => 'ملاحظات',
    'received_by' => 'استلم بواسطة',
    
    // Payment Methods
    'payment_method.cash' => 'نقداً',
    'payment_method.card' => 'بطاقة',
    'payment_method.bank_transfer' => 'تحويل بنكي',
    'payment_method.mobile_wallet' => 'محفظة إلكترونية',
    'payment_method.other' => 'أخرى',
    
    // Status
    'status.pending' => 'معلق',
    'status.completed' => 'مكتمل',
    'status.failed' => 'فشل',
    'status.refunded' => 'مسترد',
];
