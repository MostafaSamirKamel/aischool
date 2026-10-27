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
        Schema::create('instructor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            
            // Payment period
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_label')->comment('e.g., December 2025');
            
            // Session counts
            $table->integer('total_sessions')->default(0);
            $table->integer('completed_sessions')->default(0);
            $table->integer('cancelled_sessions')->default(0);
            
            // Payment calculation
            $table->decimal('rate_per_session', 10, 2)->comment('Rate per session');
            $table->decimal('base_amount', 10, 2)->default(0)->comment('Base salary if any');
            $table->decimal('sessions_amount', 10, 2)->default(0)->comment('Total from sessions');
            $table->decimal('bonus_amount', 10, 2)->default(0)->comment('Any bonuses');
            $table->decimal('deduction_amount', 10, 2)->default(0)->comment('Any deductions');
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Payment status
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable()->comment('cash, bank_transfer, etc.');
            $table->text('payment_reference')->nullable();
            $table->text('notes')->nullable();
            
            // Approval tracking
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Session details (JSON)
            $table->json('sessions_data')->nullable()->comment('Detailed breakdown of sessions');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('instructor_id');
            $table->index(['period_start', 'period_end']);
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_payments');
    }
};
