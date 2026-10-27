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
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->text('message')->nullable();
            $table->enum('type', ['text', 'image', 'document'])->default('text');
            $table->string('provider')->default('log');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->json('metadata')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
            $table->index('provider');
            $table->index('created_at');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
