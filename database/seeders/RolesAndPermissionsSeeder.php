<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // 1. صلاحيات إدارة الطلاب (Student Management)
        // ============================================
        $studentPermissions = [
            'view_students',
            'view_any_students',
            'create_students',
            'update_students',
            'delete_students',
            'restore_students',
            'force_delete_students',
        ];

        // ============================================
        // 2. صلاحيات المجموعات (Group Management)
        // ============================================
        $groupPermissions = [
            'view_groups',
            'view_any_groups',
            'create_groups',
            'update_groups',
            'delete_groups',
            'restore_groups',
            'force_delete_groups',
        ];

        // ============================================
        // 3. صلاحيات المقررات (Course Management)
        // ============================================
        $coursePermissions = [
            'view_courses',
            'view_any_courses',
            'create_courses',
            'update_courses',
            'delete_courses',
            'restore_courses',
            'force_delete_courses',
        ];

        // ============================================
        // 4. صلاحيات الحضور (Attendance Management)
        // ============================================
        $attendancePermissions = [
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            'delete_attendance',
            'restore_attendance',
            'force_delete_attendance',
        ];

        // ============================================
        // 5. صلاحيات الدرجات (Grade Management)
        // ============================================
        $gradePermissions = [
            'view_grades',
            'view_any_grades',
            'create_grades',
            'update_grades',
            'delete_grades',
            'restore_grades',
            'force_delete_grades',
        ];

        // ============================================
        // 6. صلاحيات الامتحانات (Exam Management)
        // ============================================
        $examPermissions = [
            'view_exams',
            'view_any_exams',
            'create_exams',
            'update_exams',
            'delete_exams',
            'restore_exams',
            'force_delete_exams',
        ];

        // ============================================
        // 7. صلاحيات المدرسين (Instructor Management)
        // ============================================
        $instructorPermissions = [
            'view_instructors',
            'view_any_instructors',
            'create_instructors',
            'update_instructors',
            'delete_instructors',
            'restore_instructors',
            'force_delete_instructors',
        ];

        // ============================================
        // 8. صلاحيات التسجيل (Enrollment Management)
        // ============================================
        $enrollmentPermissions = [
            'view_enrollments',
            'view_any_enrollments',
            'create_enrollments',
            'update_enrollments',
            'delete_enrollments',
            'restore_enrollments',
            'force_delete_enrollments',
            'transfer_student_group',
        ];

        // ============================================
        // 9. صلاحيات المدفوعات (Payment Management)
        // ============================================
        $paymentPermissions = [
            'view_student_payments',
            'view_any_student_payments',
            'create_student_payments',
            'update_student_payments',
            'delete_student_payments',
            'restore_student_payments',
            'force_delete_student_payments',
        ];

        // ============================================
        // 10. صلاحيات الإيرادات (Revenue Management)
        // ============================================
        $revenuePermissions = [
            'view_revenues',
            'view_any_revenues',
            'create_revenues',
            'update_revenues',
            'delete_revenues',
            'restore_revenues',
            'force_delete_revenues',
        ];

        // ============================================
        // 11. صلاحيات المصروفات (Expense Management)
        // ============================================
        $expensePermissions = [
            'view_expenses',
            'view_any_expenses',
            'create_expenses',
            'update_expenses',
            'delete_expenses',
            'restore_expenses',
            'force_delete_expenses',
        ];

        // ============================================
        // 12. صلاحيات الفصول الدراسية (Classroom Management)
        // ============================================
        $classroomPermissions = [
            'view_classrooms',
            'view_any_classrooms',
            'create_classrooms',
            'update_classrooms',
            'delete_classrooms',
            'restore_classrooms',
            'force_delete_classrooms',
        ];

        // ============================================
        // 13. صلاحيات الأقسام (Department Management)
        // ============================================
        $departmentPermissions = [
            'view_departments',
            'view_any_departments',
            'create_departments',
            'update_departments',
            'delete_departments',
            'restore_departments',
            'force_delete_departments',
        ];

        // ============================================
        // 14. صلاحيات التقارير (Report Management)
        // ============================================
        $reportPermissions = [
            'view_reports',
            'view_financial_reports',
            'view_academic_reports',
            'view_attendance_reports',
            'export_reports',
        ];

        // ============================================
        // 14.5. صلاحيات الخزائن (Vault Management & Visibility)
        // ============================================
        $vaultPermissions = [
            'view_vaults',
            'view_any_vaults',
            'create_vaults',
            'update_vaults',
            'delete_vaults',
            'view_vault_balances',
            'view_vault_transactions',
        ];

        // ============================================
        // 15. صلاحيات الإعدادات (Settings Management)
        // ============================================
        $settingsPermissions = [
            'view_settings',
            'update_settings',
            'manage_users',
            'manage_roles',
        ];

        // جمع جميع الصلاحيات
        $allPermissions = array_merge(
            $studentPermissions,
            $groupPermissions,
            $coursePermissions,
            $attendancePermissions,
            $gradePermissions,
            $examPermissions,
            $instructorPermissions,
            $enrollmentPermissions,
            $paymentPermissions,
            $revenuePermissions,
            $expensePermissions,
            $classroomPermissions,
            $departmentPermissions,
            $vaultPermissions,
            $reportPermissions,
            $settingsPermissions
        );

        // إنشاء جميع الصلاحيات
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ============================================
        // إنشاء الأدوار (Create Roles)
        // ============================================

        // 1. Super Admin - صلاحيات كاملة
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin (مدير فرع) - صلاحيات إدارية كاملة على فرع واحد
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            // إدارة الخزائن والرؤية
            ...$vaultPermissions,
            // إدارة الطلاب
            ...$studentPermissions,
            
            // إدارة المجموعات
            ...$groupPermissions,
            
            // إدارة المقررات
            ...$coursePermissions,
            
            // إدارة الحضور
            ...$attendancePermissions,
            
            // إدارة الدرجات
            ...$gradePermissions,
            
            // إدارة الامتحانات
            ...$examPermissions,
            
            // إدارة المدرسين
            ...$instructorPermissions,
            
            // إدارة التسجيل
            ...$enrollmentPermissions,
            
            // إدارة المدفوعات
            ...$paymentPermissions,
            
            // إدارة الإيرادات والمصروفات
            ...$revenuePermissions,
            ...$expensePermissions,
            
            // إدارة الفصول الدراسية والأقسام
            ...$classroomPermissions,
            ...$departmentPermissions,
            
            // التقارير
            'view_reports',
            'view_financial_reports',
            'view_academic_reports',
            'view_attendance_reports',
            'export_reports',
        ]);

        // 3. Instructor (مدرس) - صلاحيات محدودة على مجموعاته فقط
        $instructor = Role::firstOrCreate(['name' => 'instructor']);
        $instructor->givePermissionTo([
            // عرض المجموعات الخاصة به فقط
            'view_groups',
            'view_any_groups',
            
            // عرض الطلاب في مجموعاته فقط
            'view_students',
            'view_any_students',
            
            // إدارة الحضور لمجموعاته فقط
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            
            // إدارة الدرجات لمجموعاته فقط
            'view_grades',
            'view_any_grades',
            'create_grades',
            'update_grades',
            
            // عرض الامتحانات لمجموعاته
            'view_exams',
            'view_any_exams',
            
            // التقارير الخاصة بمجموعاته
            'view_academic_reports',
            'view_attendance_reports',
        ]);

        // 3.5. Supervisor (مشرف) - قراءة فقط للمجموعات المشرف عليها
        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            // عرض المجموعات المشرف عليها فقط
            'view_groups',
            'view_any_groups',

            // عرض الطلاب في المجموعات المشرف عليها
            'view_students',
            'view_any_students',

            // عرض الحضور للمجموعات المشرف عليها
            'view_attendance',
            'view_any_attendance',

            // عرض التقارير
            'view_academic_reports',
            'view_attendance_reports',
        ]);

        // 4. Student (طالب) - قراءة فقط لبياناته
        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'view_students',     // بياناته الشخصية
            'view_groups',       // مجموعاته
            'view_enrollments',  // تسجيلاته
            'view_attendance',   // حضوره
            'view_grades',       // درجاته
            'view_exams',        // امتحاناته
            'view_student_payments', // مدفوعاته
        ]);

        // 5. Parent (ولي أمر) - قراءة فقط لبيانات أبنائه
        $parent = Role::firstOrCreate(['name' => 'parent']);
        $parent->givePermissionTo([
            'view_students',     // بيانات أبنائه
            'view_groups',       // مجموعات أبنائه
            'view_enrollments',  // تسجيلات أبنائه
            'view_attendance',   // حضور أبنائه
            'view_grades',       // درجات أبنائه
            'view_exams',        // امتحانات أبنائه
            'view_student_payments', // مدفوعات أبنائه
        ]);

        $this->command->info('✅ Roles and Permissions created successfully!');
        $this->command->info('📋 Created Roles: super_admin, admin, supervisor, instructor, student, parent');
        $this->command->info('🔐 Total Permissions: ' . count($allPermissions));
    }
}
