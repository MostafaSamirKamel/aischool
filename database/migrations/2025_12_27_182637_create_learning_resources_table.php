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
        Schema::create('learning_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['pdf', 'video', 'presentation', 'document', 'image', 'audio', 'link', 'other'])->default('document');
            $table->string('file_path')->nullable(); // للملفات المرفوعة
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable(); // بالـ KB
            $table->string('file_extension')->nullable();
            $table->string('external_link')->nullable(); // للروابط الخارجية (يوتيوب، درايف، إلخ)
            $table->enum('visibility', ['all', 'group', 'course'])->default('all'); // من يمكنه رؤيته
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('course_id');
            $table->index('group_id');
            $table->index('instructor_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_resources');
    }
};
