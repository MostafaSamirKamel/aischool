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
        // إضافة vault_type للمصروفات
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('vault_type', ['vodafone', 'instapay', 'cash'])->default('cash')->after('payment_method');
        });

        // إضافة vault_type للإيرادات
        Schema::table('revenues', function (Blueprint $table) {
            $table->enum('vault_type', ['vodafone', 'instapay', 'cash'])->default('cash')->after('payment_method');
        });

        // إضافة vault_type لمدفوعات الطلاب
        Schema::table('student_payments', function (Blueprint $table) {
            $table->enum('vault_type', ['vodafone', 'instapay', 'cash'])->default('cash')->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vault_type');
        });

        Schema::table('revenues', function (Blueprint $table) {
            $table->dropColumn('vault_type');
        });

        Schema::table('student_payments', function (Blueprint $table) {
            $table->dropColumn('vault_type');
        });
    }
};
