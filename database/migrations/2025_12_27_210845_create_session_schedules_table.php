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
        Schema::create('session_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            
            // Schedule details
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes')->comment('Calculated duration in minutes');
            
            // Session info
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('regular')->comment('regular, makeup, extra, exam');
            
            // Status
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Attendance tracking
            $table->boolean('attendance_taken')->default(false);
            $table->timestamp('attendance_taken_at')->nullable();
            
            // Conflicts tracking
            $table->boolean('has_conflicts')->default(false);
            $table->json('conflicts_data')->nullable()->comment('Stores conflict details');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('group_id');
            $table->index('instructor_id');
            $table->index('classroom_id');
            $table->index('date');
            $table->index(['date', 'start_time', 'end_time']);
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_schedules');
    }
};
