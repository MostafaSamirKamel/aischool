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
        // 1. Explicit Group Supervision Assignments
        Schema::create('supervision_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'group_id']);
            $table->index(['user_id', 'group_id']);
        });

        // 2. Explicit Instructor Supervision Assignments
        Schema::create('supervision_instructor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'instructor_id']);
            $table->index(['user_id', 'instructor_id']);
        });

        // 3. Instructor Supervision Notes (strictly non-financial academic notes)
        Schema::create('instructor_supervision_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('attendance_session_id')->nullable()->constrained('attendance_sessions')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->string('category')->default('general'); // attendance_delayed, homework_grading_delayed, exam_marking_pending, session_scheduling, general, positive_observation
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('priority')->default('normal'); // low, normal, urgent
            $table->string('status')->default('pending'); // pending, reviewed, resolved
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'instructor_id']);
            $table->index(['tenant_id', 'group_id']);
            $table->index('category');
            $table->index('status');
        });

        // 4. Seed Granular Permissions for Educational Supervision and Secretary Academic Monitoring
        $permissions = [
            // Secretary Academic Monitoring
            'view_exam_followup',
            'view_homework_followup',
            'view_student_contact_numbers',
            'view_student_followup_tasks',
            'create_student_followup_task',
            'record_parent_contact',
            'complete_student_followup_task',

            // Educational Supervision
            'view_educational_supervision',
            'view_supervised_groups',
            'view_supervised_instructors',
            'view_instructor_session_progress',
            'view_academic_attendance_monitoring',
            'view_instructor_homework_progress',
            'view_instructor_exam_progress',
            'view_supervision_reports',
            'create_instructor_supervision_note',
            'view_instructor_supervision_notes',

            // Administrator Scope Management
            'manage_educational_supervision_assignments',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Attach default permissions to roles where appropriate
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        $supervisorRole = Role::where('name', 'supervisor')->first();
        if ($supervisorRole) {
            $supervisorRole->givePermissionTo([
                'view_educational_supervision',
                'view_supervised_groups',
                'view_supervised_instructors',
                'view_instructor_session_progress',
                'view_academic_attendance_monitoring',
                'view_instructor_homework_progress',
                'view_instructor_exam_progress',
                'view_supervision_reports',
                'create_instructor_supervision_note',
                'view_instructor_supervision_notes',
                'view_exam_followup',
                'view_homework_followup',
                'view_student_contact_numbers',
                'view_student_followup_tasks',
            ]);
        }

        $secretaryRole = Role::where('name', 'secretary')->first();
        if ($secretaryRole) {
            $secretaryRole->givePermissionTo([
                'view_exam_followup',
                'view_homework_followup',
                'view_student_contact_numbers',
                'view_student_followup_tasks',
                'create_student_followup_task',
                'record_parent_contact',
                'complete_student_followup_task',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_supervision_notes');
        Schema::dropIfExists('supervision_instructor_assignments');
        Schema::dropIfExists('supervision_group_assignments');
    }
};
