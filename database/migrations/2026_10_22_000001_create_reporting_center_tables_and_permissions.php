<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Saved Reports Table
        if (!Schema::hasTable('saved_reports')) {
            Schema::create('saved_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('category')->default('overview');
                $table->json('filters')->nullable();
                $table->json('columns')->nullable();
                $table->string('sort_by')->nullable();
                $table->string('sort_direction')->default('asc');
                $table->timestamps();
            });
        }

        // 2. Scheduled Reports Table
        if (!Schema::hasTable('scheduled_reports')) {
            Schema::create('scheduled_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('category')->default('overview');
                $table->string('frequency')->default('weekly'); // daily, weekly, monthly
                $table->string('format')->default('xlsx'); // xlsx, pdf, csv
                $table->json('filters')->nullable();
                $table->string('status')->default('active'); // active, paused
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->string('last_file_path')->nullable();
                $table->timestamps();
            });
        }

        // 3. Report Settings Table
        if (!Schema::hasTable('report_settings')) {
            Schema::create('report_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->string('default_period')->default('this_month');
                $table->decimal('alert_threshold_debt', 12, 2)->default(1000.00);
                $table->decimal('alert_threshold_low_score_percentage', 5, 2)->default(50.00);
                $table->integer('alert_homework_overdue_hours')->default(48);
                $table->string('pdf_header_text')->nullable();
                $table->string('pdf_footer_text')->nullable();
                $table->json('custom_settings')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Granular Reporting Permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_reporting_center',
            'view_student_growth_reports',
            'view_enrollment_reports',
            'view_financial_reports',
            'view_financial_collection_details',
            'view_installment_debt_reports',
            'view_vault_reports',
            'view_salary_reports',
            'view_cancellation_settlement_reports',
            'view_academic_reports',
            'view_educational_supervision_reports',
            'view_employee_operations_reports',
            'view_branch_comparison_reports',
            'view_management_alerts',
            'export_reporting_center',
            'manage_reporting_settings',
            'save_report_configurations',
            'schedule_reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Grant to roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        $branchManager = Role::where('name', 'branch_manager')->first();
        if ($branchManager) {
            // Branch manager has permissions except cross-tenant configuration override
            $branchManager->givePermissionTo([
                'view_reporting_center',
                'view_student_growth_reports',
                'view_enrollment_reports',
                'view_financial_reports',
                'view_financial_collection_details',
                'view_installment_debt_reports',
                'view_vault_reports',
                'view_salary_reports',
                'view_cancellation_settlement_reports',
                'view_academic_reports',
                'view_educational_supervision_reports',
                'view_employee_operations_reports',
                'view_management_alerts',
                'export_reporting_center',
                'save_report_configurations',
                'schedule_reports',
            ]);
        }

        $accountant = Role::where('name', 'accountant')->first();
        if ($accountant) {
            $accountant->givePermissionTo([
                'view_reporting_center',
                'view_financial_reports',
                'view_financial_collection_details',
                'view_installment_debt_reports',
                'view_vault_reports',
                'view_salary_reports',
                'view_cancellation_settlement_reports',
                'export_reporting_center',
                'save_report_configurations',
            ]);
        }

        $registration = Role::where('name', 'registration')->first();
        if ($registration) {
            $registration->givePermissionTo([
                'view_reporting_center',
                'view_student_growth_reports',
                'view_enrollment_reports',
                'save_report_configurations',
            ]);
        }

        $supervisor = Role::where('name', 'supervisor')->first();
        if ($supervisor) {
            $supervisor->givePermissionTo([
                'view_reporting_center',
                'view_academic_reports',
                'view_educational_supervision_reports',
                'view_employee_operations_reports',
                'export_reporting_center',
                'save_report_configurations',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('report_settings');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_reporting_center',
            'view_student_growth_reports',
            'view_enrollment_reports',
            'view_financial_reports',
            'view_financial_collection_details',
            'view_installment_debt_reports',
            'view_vault_reports',
            'view_salary_reports',
            'view_cancellation_settlement_reports',
            'view_academic_reports',
            'view_educational_supervision_reports',
            'view_employee_operations_reports',
            'view_branch_comparison_reports',
            'view_management_alerts',
            'export_reporting_center',
            'manage_reporting_settings',
            'save_report_configurations',
            'schedule_reports',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
