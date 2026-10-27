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
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('employee_code')->unique();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->json('qualifications')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->date('hired_date')->nullable();
            $table->enum('contract_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('department_id');
            $table->index('employee_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
