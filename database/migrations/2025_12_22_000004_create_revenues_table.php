<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('revenue_code')->unique();
            $table->foreignId('revenue_category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('revenue_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'online', 'other'])->default('cash');
            $table->string('invoice_number')->nullable();
            $table->string('customer_name')->nullable();
            
            // ربط مع مدفوعات الطلاب
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('received');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'revenue_date']);
            $table->index(['tenant_id', 'status']);
            $table->index('revenue_category_id');
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
