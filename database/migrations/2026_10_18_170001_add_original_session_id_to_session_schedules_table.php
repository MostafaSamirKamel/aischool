<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('session_schedules', 'original_session_id')) {
                $table->foreignId('original_session_id')
                    ->nullable()
                    ->after('type')
                    ->constrained('session_schedules')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('session_schedules', 'rescheduled_at')) {
                $table->timestamp('rescheduled_at')->nullable()->after('cancellation_reason');
            }

            if (!Schema::hasColumn('session_schedules', 'rescheduled_by')) {
                $table->foreignId('rescheduled_by')
                    ->nullable()
                    ->after('rescheduled_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('session_schedules', function (Blueprint $table) {
            $table->dropForeign(['original_session_id']);
            $table->dropForeign(['rescheduled_by']);
            $table->dropColumn(['original_session_id', 'rescheduled_at', 'rescheduled_by']);
        });
    }
};
