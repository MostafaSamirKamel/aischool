<?php

return [
    'navigation_label' => 'التحويلات بين الخزائن',
    'model_label' => 'تحويل',
    'plural_model_label' => 'التحويلات بين الخزائن',
    'navigation_group' => 'الإدارة المالية',

    // Fields
    'transfer_code' => 'كود التحويل',
    'transfer_date' => 'تاريخ التحويل',
    'from_vault_id' => 'من خزينة',
    'to_vault_id' => 'إلى خزينة',
    'amount' => 'المبلغ',
    'status' => 'الحالة',
    'notes' => 'ملاحظات',
    'cancellation_reason' => 'سبب الإلغاء',
    'transferred_by' => 'تم التحويل بواسطة',
    'approved_by' => 'تم الاعتماد بواسطة',
    'approved_at' => 'تاريخ الاعتماد',

    // Status
    'status.pending' => 'قيد الانتظار',
    'status.completed' => 'مكتمل',
    'status.cancelled' => 'ملغي',

    // Messages
    'created_successfully' => 'تم إنشاء التحويل بنجاح',
    'updated_successfully' => 'تم تحديث التحويل بنجاح',
    'deleted_successfully' => 'تم حذف التحويل بنجاح',
    'approved_successfully' => 'تم اعتماد التحويل بنجاح',
    'cancelled_successfully' => 'تم إلغاء التحويل بنجاح',

    // Errors
    'cannot_edit' => 'لا يمكن تعديل هذا التحويل',
    'cannot_delete' => 'لا يمكن حذف هذا التحويل',
    'same_vault_error' => 'لا يمكن التحويل من وإلى نفس الخزينة',
];
