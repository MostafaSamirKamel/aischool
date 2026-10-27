<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add academic reference columns to student_follow_up_tasks if not present
        Schema::table('student_follow_up_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('student_follow_up_tasks', 'source_type')) {
                $table->string('source_type')->nullable()->after('trigger_type'); // 'exam', 'homework'
            }
            if (!Schema::hasColumn('student_follow_up_tasks', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type'); // exam_id or assignment_id
            }
            if (!Schema::hasColumn('student_follow_up_tasks', 'academic_status')) {
                $table->string('academic_status')->nullable()->after('source_id');
            }
        });

        // 2. Create student_follow_up_contacts table for chronological communication logs
        if (!Schema::hasTable('student_follow_up_contacts')) {
            Schema::create('student_follow_up_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('task_id')->constrained('student_follow_up_tasks')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('contacted_guardian_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('guardian_name')->nullable();
                $table->string('guardian_phone')->nullable();
                $table->string('contact_method')->default('phone_call'); // phone_call, whatsapp, in_person, other
                $table->string('contact_result')->default('contacted'); // contacted, no_answer, unreachable, promised_to_submit, requires_follow_up, resolved
                $table->text('notes')->nullable();
                $table->dateTime('contacted_at');
                $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
                $table->date('next_follow_up_date')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'task_id']);
                $table->index(['student_id', 'contacted_at']);
                $table->index('contact_result');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_follow_up_contacts');

        Schema::table('student_follow_up_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('student_follow_up_tasks', 'source_type')) {
                $table->dropColumn('source_type');
            }
            if (Schema::hasColumn('student_follow_up_tasks', 'source_id')) {
                $table->dropColumn('source_id');
            }
            if (Schema::hasColumn('student_follow_up_tasks', 'academic_status')) {
                $table->dropColumn('academic_status');
            }
        });
    }
};
