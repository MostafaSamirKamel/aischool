<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreignId('salary_payment_id')
                ->nullable()
                ->after('recorded_by')
                ->constrained('salary_payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['salary_payment_id']);
            $table->dropColumn('salary_payment_id');
        });
    }
};
