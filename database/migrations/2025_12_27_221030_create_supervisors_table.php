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
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Personal Information
            $table->string('employee_number')->unique();
            $table->string('national_id')->unique();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('nationality')->default('Saudi');
            
            // Contact Information
            $table->string('phone')->unique();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            
            // Employment Information
            $table->date('hire_date');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            
            // Education & Qualifications
            $table->string('education_level')->nullable();
            $table->string('specialization')->nullable();
            $table->text('certifications')->nullable();
            $table->integer('years_of_experience')->default(0);
            
            // Financial Information
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->decimal('allowances', 10, 2)->default(0);
            $table->enum('payment_type', ['monthly', 'hourly', 'daily'])->default('monthly');
            
            // Work Schedule
            $table->json('work_days')->nullable()->comment('Days of the week');
            $table->time('work_start_time')->nullable();
            $table->time('work_end_time')->nullable();
            
            // Permissions & Access
            $table->json('permissions')->nullable()->comment('Supervisor specific permissions');
            $table->boolean('can_view_attendance')->default(true);
            $table->boolean('can_manage_resources')->default(true);
            $table->boolean('can_view_grades')->default(true);
            $table->boolean('can_send_messages')->default(true);
            
            // Documents
            $table->string('profile_photo')->nullable();
            $table->string('resume_file')->nullable();
            $table->string('id_copy')->nullable();
            $table->json('other_documents')->nullable();
            
            // Notes & Bio
            $table->text('bio')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('employee_number');
            $table->index('status');
            $table->index('employment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};
