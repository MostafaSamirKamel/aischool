<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add attendance_session_id to exams if not exists (optional session association)
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!Schema::hasColumn('exams', 'attendance_session_id')) {
                    $table->foreignId('attendance_session_id')->nullable()->after('group_id')->constrained('attendance_sessions')->nullOnDelete();
                }
                if (!Schema::hasColumn('exams', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('status');
                }
            });
        }

        // 2. Add attempt_number to assignment_submissions if not exists
        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('assignment_submissions', 'attempt_number')) {
                    $table->unsignedInteger('attempt_number')->default(1)->after('student_id');
                }
                if (!Schema::hasColumn('assignment_submissions', 'is_official')) {
                    $table->boolean('is_official')->default(true)->after('graded');
                }
            });
        }

        // 3. Add attempt_number and is_official to exam_attempts if not exists
        if (Schema::hasTable('exam_attempts')) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                if (!Schema::hasColumn('exam_attempts', 'attempt_number')) {
                    $table->unsignedInteger('attempt_number')->default(1)->after('student_id');
                }
                if (!Schema::hasColumn('exam_attempts', 'is_official')) {
                    $table->boolean('is_official')->default(true)->after('passed');
                }
                if (!Schema::hasColumn('exam_attempts', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('completed_at');
                }
            });
        }

        // 4. Create homework_resubmission_grants table
        if (!Schema::hasTable('homework_resubmission_grants')) {
            Schema::create('homework_resubmission_grants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
                $table->dateTime('expires_at');
                $table->text('reason');
                $table->dateTime('consumed_at')->nullable();
                $table->unsignedBigInteger('consumed_submission_id')->nullable();
                $table->enum('status', ['active', 'consumed', 'expired', 'revoked'])->default('active');
                $table->timestamps();

                $table->index(['assignment_id', 'student_id', 'status']);
                $table->index(['expires_at', 'status']);
            });
        }

        // 5. Create homework_deadline_extensions table
        if (!Schema::hasTable('homework_deadline_extensions')) {
            Schema::create('homework_deadline_extensions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
                $table->enum('scope', ['group', 'student'])->default('group');
                $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
                $table->dateTime('previous_due_date')->nullable();
                $table->dateTime('new_due_date');
                $table->text('reason');
                $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['assignment_id', 'scope', 'student_id']);
            });
        }

        // 6. Create exam_retake_grants table
        if (!Schema::hasTable('exam_retake_grants')) {
            Schema::create('exam_retake_grants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
                $table->dateTime('expires_at');
                $table->text('reason');
                $table->dateTime('consumed_at')->nullable();
                $table->unsignedBigInteger('consumed_attempt_id')->nullable();
                $table->enum('status', ['active', 'consumed', 'expired', 'revoked'])->default('active');
                $table->timestamps();

                $table->index(['exam_id', 'student_id', 'status']);
                $table->index(['expires_at', 'status']);
            });
        }

        // 7. Create academic_grade_corrections audit table
        if (!Schema::hasTable('academic_grade_corrections')) {
            Schema::create('academic_grade_corrections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->string('target_type'); // 'assignment_submission' or 'exam_attempt'
                $table->unsignedBigInteger('target_id');
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('assignment_id')->nullable()->constrained('assignments')->cascadeOnDelete();
                $table->foreignId('exam_id')->nullable()->constrained('exams')->cascadeOnDelete();
                $table->foreignId('corrected_by')->constrained('users')->cascadeOnDelete();
                $table->decimal('previous_score', 8, 2)->nullable();
                $table->decimal('new_score', 8, 2);
                $table->text('reason');
                $table->timestamps();

                $table->index(['target_type', 'target_id']);
                $table->index(['student_id', 'target_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_grade_corrections');
        Schema::dropIfExists('exam_retake_grants');
        Schema::dropIfExists('homework_deadline_extensions');
        Schema::dropIfExists('homework_resubmission_grants');

        if (Schema::hasTable('exam_attempts')) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('exam_attempts', 'published_at')) $table->dropColumn('published_at');
                if (Schema::hasColumn('exam_attempts', 'is_official')) $table->dropColumn('is_official');
                if (Schema::hasColumn('exam_attempts', 'attempt_number')) $table->dropColumn('attempt_number');
            });
        }

        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('assignment_submissions', 'is_official')) $table->dropColumn('is_official');
                if (Schema::hasColumn('assignment_submissions', 'attempt_number')) $table->dropColumn('attempt_number');
            });
        }

        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (Schema::hasColumn('exams', 'published_at')) $table->dropColumn('published_at');
                if (Schema::hasColumn('exams', 'attendance_session_id')) $table->dropColumn('attendance_session_id');
            });
        }
    }
};
