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
        // 1. Supervisory Observations Table
        if (!Schema::hasTable('supervisory_observations')) {
            Schema::create('supervisory_observations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('observation_code', 50)->unique();
                $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
                $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
                $table->foreignId('attendance_session_id')->nullable()->constrained('attendance_sessions')->nullOnDelete();
                $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
                $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
                $table->string('category', 50); // attendance_not_recorded, homework_grading_delay, examination_review, teaching_quality, incomplete_educational_content, session_preparation, student_academic_concern, other
                $table->string('title', 255);
                $table->text('description');
                $table->text('required_action');
                $table->date('expected_resolution_date')->nullable();
                $table->string('priority', 20)->default('normal'); // low, normal, urgent
                $table->string('status', 30)->default('new'); // new, waiting_supervisor_review, completed
                $table->text('instructor_response')->nullable();
                $table->timestamp('instructor_responded_at')->nullable();
                $table->text('verification_notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('return_reason')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->json('attachments')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'instructor_id']);
                $table->index(['tenant_id', 'supervisor_id']);
                $table->index(['tenant_id', 'group_id']);
            });
        }

        // 2. Supervisory Observation Histories Table
        if (!Schema::hasTable('supervisory_observation_histories')) {
            Schema::create('supervisory_observation_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('observation_id')->constrained('supervisory_observations')->cascadeOnDelete();
                $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
                $table->string('action', 50); // created, instructor_responded, submitted_for_review, returned_for_correction, approved_completed, deadline_changed, evidence_updated
                $table->string('previous_status', 50)->nullable();
                $table->string('new_status', 50)->nullable();
                $table->text('comment')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['observation_id', 'created_at']);
                $table->index(['tenant_id', 'actor_id']);
            });
        }

        // 3. Instructor Evaluations Table
        if (!Schema::hasTable('instructor_evaluations')) {
            Schema::create('instructor_evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('evaluation_code', 50)->unique();
                $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
                $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
                $table->string('evaluation_type', 30); // session, monthly
                $table->string('evaluation_period', 20)->nullable(); // e.g. '2026-10'
                $table->foreignId('attendance_session_id')->nullable()->constrained('attendance_sessions')->nullOnDelete();
                $table->string('review_basis', 50)->default('direct_observation'); // direct_observation, recording_review, educational_work_review, monthly_evidence_review
                
                // 6 Evaluated Criteria (1 to 5, nullable for Not Observed)
                $table->unsignedTinyInteger('score_explanation_quality')->nullable();
                $table->unsignedTinyInteger('score_student_interaction')->nullable();
                $table->unsignedTinyInteger('score_practical_application')->nullable();
                $table->unsignedTinyInteger('score_curriculum_adherence')->nullable();
                $table->unsignedTinyInteger('score_time_management')->nullable();
                $table->unsignedTinyInteger('score_homework_followup')->nullable();

                $table->decimal('calculated_average', 4, 2)->nullable();
                $table->unsignedTinyInteger('criteria_observed_count')->default(0);

                // Written sections
                $table->text('strengths')->nullable();
                $table->text('areas_for_improvement')->nullable();
                $table->text('recommendations')->nullable();

                $table->string('status', 20)->default('draft'); // draft, published
                $table->unsignedInteger('version')->default(1);
                $table->timestamp('published_at')->nullable();
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('management_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'instructor_id', 'evaluation_period']);
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'evaluation_type']);
            });
        }

        // 4. Instructor Evaluation Appeals & Comments Table
        if (!Schema::hasTable('instructor_evaluation_appeals')) {
            Schema::create('instructor_evaluation_appeals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('evaluation_id')->constrained('instructor_evaluations')->cascadeOnDelete();
                $table->unsignedInteger('evaluation_version')->default(1);
                $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
                $table->string('type', 20)->default('appeal'); // comment, appeal
                $table->text('reason');
                $table->json('evidence')->nullable();
                $table->text('supervisor_response')->nullable();
                $table->timestamp('supervisor_responded_at')->nullable();
                $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 30)->default('submitted'); // submitted, supervisor_responded, under_management_review, accepted, rejected, returned_for_clarification
                $table->string('management_decision', 30)->nullable(); // accepted, rejected, returned_for_clarification
                $table->text('management_decision_reason')->nullable();
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'evaluation_id']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // 5. Instructor Evaluation Versions Table (Preserves historical versions after management corrections)
        if (!Schema::hasTable('instructor_evaluation_versions')) {
            Schema::create('instructor_evaluation_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('evaluation_id')->constrained('instructor_evaluations')->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->json('snapshot');
                $table->text('change_reason')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['evaluation_id', 'version']);
            });
        }

        // 6. Educational Supervision Reports Table (Weekly & Monthly)
        if (!Schema::hasTable('supervision_reports')) {
            Schema::create('supervision_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('report_code', 50)->unique();
                $table->string('report_type', 20); // weekly, monthly
                $table->date('period_start');
                $table->date('period_end');
                $table->string('period_key', 30); // e.g. '2026-W42', '2026-10'
                $table->unsignedInteger('version')->default(1);
                $table->foreignId('parent_report_id')->nullable()->constrained('supervision_reports')->nullOnDelete();
                $table->json('metrics_snapshot');

                // Written sections
                $table->text('summary')->nullable();
                $table->text('instructor_observations')->nullable();
                $table->text('group_observations')->nullable();
                $table->text('problems_encountered')->nullable();
                $table->text('actions_taken')->nullable();
                $table->text('recommendations')->nullable();

                $table->string('status', 30)->default('draft'); // draft, published, revision_requested
                $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('published_at')->nullable();

                // Management interaction
                $table->json('management_comments')->nullable();
                $table->json('clarification_requests')->nullable();
                $table->text('revision_reason')->nullable();
                $table->foreignId('revision_requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revision_requested_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'report_type', 'period_key']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // 7. Supervision Report Versions Table
        if (!Schema::hasTable('supervision_report_versions')) {
            Schema::create('supervision_report_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('report_id')->constrained('supervision_reports')->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->json('snapshot');
                $table->text('change_reason')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['report_id', 'version']);
            });
        }

        // 8. Register Distinct Granular Permissions
        $newPermissions = [
            'branch_educational_supervisor',
            'view_supervision_dashboard',
            'create_supervisory_observations',
            'respond_supervisory_observations',
            'approve_supervisory_observations',
            'return_supervisory_observations',
            'create_instructor_evaluations',
            'publish_instructor_evaluations',
            'view_instructor_evaluations',
            'comment_instructor_evaluations',
            'appeal_instructor_evaluations',
            'respond_evaluation_appeals',
            'decide_evaluation_appeals',
            'prepare_supervision_reports',
            'publish_supervision_reports',
            'review_management_supervision_reports',
            'request_supervision_report_revision',
            'request_supervision_report_clarification',
        ];

        foreach ($newPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Assign to Super Admin & Admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($newPermissions);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($newPermissions);
        }

        $branchManager = Role::where('name', 'branch_manager')->first();
        if ($branchManager) {
            $branchManager->givePermissionTo([
                'view_supervision_dashboard',
                'review_management_supervision_reports',
                'request_supervision_report_revision',
                'request_supervision_report_clarification',
                'decide_evaluation_appeals',
                'view_instructor_evaluations',
            ]);
        }

        // Educational Supervisor Role
        $supervisor = Role::where('name', 'supervisor')->first();
        if ($supervisor) {
            $supervisor->givePermissionTo([
                'branch_educational_supervisor',
                'view_supervision_dashboard',
                'create_supervisory_observations',
                'approve_supervisory_observations',
                'return_supervisory_observations',
                'create_instructor_evaluations',
                'publish_instructor_evaluations',
                'view_instructor_evaluations',
                'respond_evaluation_appeals',
                'prepare_supervision_reports',
                'publish_supervision_reports',
            ]);
        }

        // Instructor Role
        $instructorRole = Role::where('name', 'instructor')->first();
        if ($instructorRole) {
            $instructorRole->givePermissionTo([
                'respond_supervisory_observations',
                'view_instructor_evaluations',
                'comment_instructor_evaluations',
                'appeal_instructor_evaluations',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervision_report_versions');
        Schema::dropIfExists('supervision_reports');
        Schema::dropIfExists('instructor_evaluation_versions');
        Schema::dropIfExists('instructor_evaluation_appeals');
        Schema::dropIfExists('instructor_evaluations');
        Schema::dropIfExists('supervisory_observation_histories');
        Schema::dropIfExists('supervisory_observations');
    }
};
