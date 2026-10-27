<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('installment_id')->constrained('installments')->cascadeOnDelete();
            $table->string('recipient_type'); // parent, student
            $table->unsignedBigInteger('recipient_id');
            $table->string('reminder_type'); // upcoming, due_today, overdue
            $table->string('channel')->default('in_app'); // in_app, email, sms, whatsapp
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('sent'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'installment_id']);
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_reminder_logs');
    }
};
