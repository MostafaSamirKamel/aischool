<?php

/**
 * نظام الأدوار والصلاحيات
 * Roles & Permissions System
 * 
 * هذا الملف يوضح الأدوار والصلاحيات المطلوبة في النظام
 */

return [
    /**
     * 1. Super Admin (الإدارة العليا)
     * Role: super_admin
     */
    'super_admin' => [
        'panel' => 'admin',
        'description' => 'الإدارة العليا - الوصول الكامل لكل الفروع',
        'permissions' => [
            // إدارة الفروع
            'view_any_tenant',
            'create_tenant',
            'update_tenant',
            'delete_tenant',
            
            // إدارة المستخدمين
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            
            // الوصول لكل الموارد عبر كل الفروع
            'view_all_branches_data',
            'manage_system_settings',
            'view_all_reports',
        ],
        'can_access' => [
            'Tenants', 'Users', 'Settings',
            'All Resources with branch filter'
        ],
        'restrictions' => 'لا يوجد - وصول كامل',
    ],

    /**
     * 2. Branch Admin/Supervisor (مشرف الفرع)
     * Role: admin
     */
    'admin' => [
        'panel' => 'admin',
        'description' => 'مشرف الفرع - إدارة فرع واحد فقط',
        'permissions' => [
            // إدارة الطلاب
            'view_any_student',
            'create_student',
            'update_student',
            'delete_student',
            
            // إدارة أولياء الأمور
            'view_any_parent',
            'create_parent',
            'update_parent',
            
            // إدارة التسجيلات
            'view_any_enrollment',
            'create_enrollment',
            'update_enrollment',
            'delete_enrollment',
            
            // إدارة المجموعات
            'view_any_group',
            'create_group',
            'update_group',
            'delete_group',
            
            // إدارة المدفوعات
            'view_any_payment',
            'create_payment',
            'update_payment',
            'delete_payment',
            
            // متابعة الحضور
            'view_any_attendance',
            'view_attendance',
            
            // التقارير
            'view_branch_reports',
            
            // الحسابات
            'view_any_revenue',
            'create_revenue',
            'view_any_expense',
            'create_expense',
        ],
        'can_access' => [
            'Students', 'Parents', 'Enrollments', 
            'Groups', 'Payments', 'Attendance',
            'Revenues', 'Expenses', 'Reports'
        ],
        'restrictions' => 'فقط بيانات فرعه (tenant_id)',
    ],

    /**
     * 3. Instructor (المدرب)
     * Role: instructor
     */
    'instructor' => [
        'panel' => 'admin', // نفس الـ panel لكن صلاحيات محدودة
        'description' => 'المدرب - تسجيل حضور ودرجات طلابه فقط',
        'permissions' => [
            // رؤية المجموعات المسندة له
            'view_own_groups',
            'view_own_students',
            
            // تسجيل الحضور
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            
            // إدخال الدرجات
            'view_any_grade',
            'create_grade',
            'update_grade',
            
            // رؤية الجدول
            'view_schedule',
        ],
        'can_access' => [
            'Groups (المسندة له فقط)',
            'Students (في مجموعاته فقط)',
            'Attendance (لمجموعاته)',
            'Grades (لطلابه)',
            'Schedule (جدوله)'
        ],
        'restrictions' => [
            'فقط المجموعات المسندة له (instructor_id)',
            'لا يستطيع: حذف أي بيانات',
            'لا يستطيع: إدارة مالية',
            'لا يستطيع: إضافة/حذف طلاب',
        ],
    ],

    /**
     * 4. Student (الطالب)
     * Role: student
     */
    'student' => [
        'panel' => 'student', // Panel منفصل
        'description' => 'الطالب - رؤية بياناته فقط',
        'permissions' => [
            'view_own_profile',
            'update_own_profile',
            'view_own_schedule',
            'view_own_grades',
            'view_own_attendance',
            'view_own_payments',
            'view_own_courses',
        ],
        'can_access' => [
            'Profile',
            'Schedule',
            'Grades',
            'Attendance',
            'Payments',
            'Courses'
        ],
        'restrictions' => [
            'فقط بياناته الشخصية (student_id)',
            'عرض فقط - لا يستطيع التعديل',
        ],
    ],

    /**
     * 5. Parent (ولي الأمر)
     * Role: parent
     */
    'parent' => [
        'panel' => 'parent', // Panel منفصل
        'description' => 'ولي الأمر - متابعة أبنائه',
        'permissions' => [
            'view_own_profile',
            'update_own_profile',
            'view_children_data',
            'view_children_grades',
            'view_children_attendance',
            'view_children_payments',
            'view_children_schedule',
            'contact_administration',
        ],
        'can_access' => [
            'Profile',
            'Children List',
            'Children Grades',
            'Children Attendance',
            'Children Payments',
            'Children Schedule',
            'Messages'
        ],
        'restrictions' => [
            'فقط بيانات أبنائه (parent_id)',
            'عرض فقط - لا يستطيع التعديل',
        ],
    ],
];