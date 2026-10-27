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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('notes');
            $table->text('cancellation_notes')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_notes');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            
            $table->string('settlement_type')->nullable()->after('cancelled_by'); // keep_debt, waive_debt, session_based, full_refund, partial_refund, custom
            $table->string('settlement_status')->nullable()->after('settlement_type'); // settled, pending_refund, completed
            $table->decimal('waived_amount', 10, 2)->default(0)->after('settlement_status');
            $table->decimal('retained_amount', 10, 2)->default(0)->after('waived_amount');
            $table->decimal('refundable_amount', 10, 2)->default(0)->after('retained_amount');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refundable_amount');
            $table->string('refund_status')->default('not_applicable')->after('refunded_amount'); // not_applicable, pending, partially_refunded, completed
            
            $table->foreignId('refund_vault_id')->nullable()->after('refund_status')->constrained('vaults')->nullOnDelete();
            $table->string('refund_payment_method')->nullable()->after('refund_vault_id');
            $table->timestamp('refunded_at')->nullable()->after('refund_payment_method');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->json('settlement_details')->nullable()->after('refunded_by');
        });

        Schema::create('enrollment_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason');
            $table->text('cancellation_notes')->nullable();
            $table->string('settlement_type');
            $table->string('settlement_status')->default('settled');
            
            $table->decimal('original_price', 10, 2)->default(0);
            $table->decimal('discount_applied', 10, 2)->default(0);
            $table->decimal('net_price', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('outstanding_before', 10, 2)->default(0);
            
            $table->decimal('retained_amount', 10, 2)->default(0);
            $table->decimal('waived_amount', 10, 2)->default(0);
            $table->decimal('remaining_debt', 10, 2)->default(0);
            $table->decimal('refundable_amount', 10, 2)->default(0);
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->string('refund_status')->default('not_applicable');
            
            $table->foreignId('refund_vault_id')->nullable()->constrained('vaults')->nullOnDelete();
            $table->string('refund_payment_method')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->json('financial_snapshot')->nullable();
            $table->json('settlement_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'enrollment_id']);
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_cancellations');

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropForeign(['refund_vault_id']);
            $table->dropForeign(['refunded_by']);
            $table->dropColumn([
                'cancellation_reason',
                'cancellation_notes',
                'cancelled_at',
                'cancelled_by',
                'settlement_type',
                'settlement_status',
                'waived_amount',
                'retained_amount',
                'refundable_amount',
                'refunded_amount',
                'refund_status',
                'refund_vault_id',
                'refund_payment_method',
                'refunded_at',
                'refunded_by',
                'settlement_details',
            ]);
        });
    }
};
