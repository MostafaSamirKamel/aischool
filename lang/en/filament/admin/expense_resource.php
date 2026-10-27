<?php

return [
    'navigation_group' => 'Financial Management',
    'navigation_label' => 'Expenses',
    'model_label' => 'Expense',
    'plural_model_label' => 'Expenses',

    // Fields
    'expense_code' => 'Expense Code',
    'expense_category_id' => 'Expense Category',
    'category' => 'Category',
    'category.name' => 'Category',
    'title' => 'Title',
    'description' => 'Description',
    'amount' => 'Amount',
    'expense_date' => 'Expense Date',
    'payment_method' => 'Payment Method',
    'invoice_number' => 'Invoice Number',
    'vendor_name' => 'Vendor Name',
    'notes' => 'Notes',
    'status' => 'Status',
    'approved_by' => 'Approved By',
    'approved_at' => 'Approved At',
    'created_by' => 'Created By',
    'created_by.name' => 'Created By',
    'created_at' => 'Created At',

    // Sections
    'basic_info' => 'Basic Information',
    'financial_details' => 'Financial Details',
    'vendor_info' => 'Vendor Information',
    'approval' => 'Approval',

    // Payment Methods
    'payment_methods' => [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ],

    // Statuses
    'statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ],

    // Other
    'auto_generated' => 'Will be generated automatically',
    'total' => 'Total',
    'from_date' => 'From Date',
    'to_date' => 'To Date',
];
