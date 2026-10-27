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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->integer('installment_number'); // رقم القسط (1، 2، 3...)
            $table->decimal('amount', 10, 2); // قيمة القسط
            $table->date('due_date'); // تاريخ الاستحقاق
            $table->decimal('paid_amount', 10, 2)->default(0); // المبلغ المدفوع
            $table->date('paid_date')->nullable(); // تاريخ الدفع
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial', 'cancelled'])->default('pending'); // حالة القسط
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            
            $table->index(['enrollment_id', 'installment_number']);
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
