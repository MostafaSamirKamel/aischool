<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->date('period_start')->nullable()->after('vault_id');
            $table->date('period_end')->nullable()->after('period_start');
            $table->unsignedInteger('sessions_count')->default(0)->after('period_end');
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end', 'sessions_count']);
        });
    }
};
