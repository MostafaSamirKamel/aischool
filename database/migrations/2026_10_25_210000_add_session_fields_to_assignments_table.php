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
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'session_schedule_id')) {
                $table->unsignedBigInteger('session_schedule_id')->nullable()->after('group_id');
                $table->foreign('session_schedule_id')
                    ->references('id')
                    ->on('session_schedules')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('assignments', 'attendance_session_id')) {
                $table->unsignedBigInteger('attendance_session_id')->nullable()->after('session_schedule_id');
                $table->foreign('attendance_session_id')
                    ->references('id')
                    ->on('attendance_sessions')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('assignments', 'session_number')) {
                $table->integer('session_number')->nullable()->after('attendance_session_id')->comment('رقم السيشن أو المحاضرة');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'session_schedule_id')) {
                $table->dropForeign(['session_schedule_id']);
                $table->dropColumn('session_schedule_id');
            }

            if (Schema::hasColumn('assignments', 'attendance_session_id')) {
                $table->dropForeign(['attendance_session_id']);
                $table->dropColumn('attendance_session_id');
            }

            if (Schema::hasColumn('assignments', 'session_number')) {
                $table->dropColumn('session_number');
            }
        });
    }
};
