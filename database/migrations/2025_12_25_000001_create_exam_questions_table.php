<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->decimal('points', 5, 2)->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('exam_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};