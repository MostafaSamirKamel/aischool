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
        Schema::table('learning_resources', function (Blueprint $table) {
            $table->unsignedBigInteger('session_schedule_id')->nullable()->after('group_id');
            $table->integer('session_number')->nullable()->after('session_schedule_id')->comment('رقم المحاضرة');
            
            $table->foreign('session_schedule_id')
                ->references('id')
                ->on('session_schedules')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_resources', function (Blueprint $table) {
            $table->dropForeign(['session_schedule_id']);
            $table->dropColumn(['session_schedule_id', 'session_number']);
        });
    }
};
