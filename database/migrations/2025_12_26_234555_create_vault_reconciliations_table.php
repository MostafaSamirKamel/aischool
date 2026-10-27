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
        Schema::create('vault_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vault_id')->constrained('vaults')->cascadeOnDelete();
            $table->date('reconciliation_date');
            $table->decimal('system_balance', 15, 2)->comment('الرصيد في النظام');
            $table->decimal('actual_balance', 15, 2)->comment('الرصيد الفعلي من السكرتيرة');
            $table->decimal('difference', 15, 2)->comment('الفرق بين النظام والفعلي');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['tenant_id', 'vault_id'], 'vault_recon_tenant_vault_idx');
            $table->index('reconciliation_date', 'vault_recon_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_reconciliations');
    }
};
