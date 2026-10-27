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
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->enum('sender_type', ['instructor', 'admin', 'system'])->default('instructor');
            
            // Message content
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('type', ['announcement', 'reminder', 'warning', 'info'])->default('info');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Attachments
            $table->json('attachments')->nullable()->comment('File paths or URLs');
            
            // Delivery tracking
            $table->boolean('send_notification')->default(true);
            $table->boolean('send_email')->default(false);
            $table->integer('recipients_count')->default(0);
            $table->integer('read_count')->default(0);
            
            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled'])->default('draft');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('group_id');
            $table->index('sender_id');
            $table->index('type');
            $table->index('status');
            $table->index('sent_at');
        });

        // Message reads tracking
        Schema::create('group_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('group_messages')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            
            $table->unique(['message_id', 'student_id']);
            $table->index('message_id');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_messages');
    }
};
