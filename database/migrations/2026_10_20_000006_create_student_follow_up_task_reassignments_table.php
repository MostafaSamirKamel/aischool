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
        // 1. Create student_follow_up_task_reassignments table for complete audit history
        if (!Schema::hasTable('student_follow_up_task_reassignments')) {
            Schema::create('student_follow_up_task_reassignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('task_id')->constrained('student_follow_up_tasks')->cascadeOnDelete();
                $table->foreignId('previous_assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('new_assigned_to')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reassigned_by')->constrained('users')->cascadeOnDelete();
                $table->dateTime('reassigned_at');
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'task_id']);
                $table->index('reassigned_at');
            });
        }

        // 2. Ensure task status values align with the 2-status rule ('new', 'contacted')
        // In existing records, normalize any 'pending' / 'in_progress' to 'new', and 'completed' to 'contacted'
        if (Schema::hasTable('student_follow_up_tasks')) {
            \Illuminate\Support\Facades\DB::table('student_follow_up_tasks')
                ->whereIn('status', ['pending', 'in_progress'])
                ->update(['status' => 'new']);

            \Illuminate\Support\Facades\DB::table('student_follow_up_tasks')
                ->where('status', 'completed')
                ->update(['status' => 'contacted']);
        }

        // 3. Register Granular Follow-up Permissions
        $permissions = [
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_student_contact_numbers',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
            'view_daily_follow_up_reports',
            'view_student_financial_follow_up',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Give permissions to Admin and Super Admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        $branchManagerRole = Role::where('name', 'branch_manager')->first();
        if ($branchManagerRole) {
            $branchManagerRole->givePermissionTo($permissions);
        }

        // Educational Supervisor
        $supervisorRole = Role::where('name', 'supervisor')->first();
        if ($supervisorRole) {
            $supervisorRole->givePermissionTo([
                'view_daily_follow_up_dashboard',
                'view_student_academic_monitoring',
                'view_student_contact_numbers',
                'create_student_followup_task',
                'record_parent_contact',
                'reassign_follow_up_case',
                'view_daily_follow_up_reports',
            ]);
        }

        // Secretary
        $secretaryRole = Role::where('name', 'secretary')->first();
        if ($secretaryRole) {
            $secretaryRole->givePermissionTo([
                'view_daily_follow_up_dashboard',
                'view_student_academic_monitoring',
                'view_student_contact_numbers',
                'create_student_followup_task',
                'record_parent_contact',
                'view_daily_follow_up_reports',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_follow_up_task_reassignments');
    }
};
