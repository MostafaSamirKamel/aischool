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
        Schema::create('instructor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->cascadeOnDelete();
            
            // Reviewer (student or parent)
            $table->enum('reviewer_type', ['student', 'parent']);
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            
            // Rating (1-5 stars)
            $table->unsignedTinyInteger('rating')->comment('1 to 5 stars');
            
            // Review details
            $table->text('comment')->nullable();
            $table->json('aspects')->nullable()->comment('Ratings for specific aspects like teaching_quality, communication, etc.');
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Metadata
            $table->boolean('is_anonymous')->default(false);
            $table->ipAddress('reviewer_ip')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('instructor_id');
            $table->index('course_id');
            $table->index('group_id');
            $table->index('reviewer_type');
            $table->index('status');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_reviews');
    }
};
