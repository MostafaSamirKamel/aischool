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
        Schema::create('vault_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // الخزينة المحول منها
            $table->foreignId('from_vault_id')->constrained('vaults')->cascadeOnDelete();
            
            // الخزينة المحول إليها
            $table->foreignId('to_vault_id')->constrained('vaults')->cascadeOnDelete();
            
            // بيانات التحويل
            $table->decimal('amount', 10, 2);
            $table->date('transfer_date');
            $table->string('transfer_code')->unique();
            
            // حالة التحويل
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            
            // ملاحظات
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // من قام بالتحويل
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            
            // من اعتمد/ألغى التحويل
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'from_vault_id']);
            $table->index(['tenant_id', 'to_vault_id']);
            $table->index(['tenant_id', 'status']);
            $table->index('transfer_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_transfers');
    }
};
