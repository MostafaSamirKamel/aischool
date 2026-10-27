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
        Schema::table('instructors', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('status')->comment('أجر الساعة');
            $table->decimal('base_salary', 10, 2)->nullable()->after('hourly_rate')->comment('الراتب الأساسي');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'base_salary']);
        });
    }
};
