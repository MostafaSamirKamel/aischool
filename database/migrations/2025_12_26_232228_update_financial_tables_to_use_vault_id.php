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
        // حذف vault_type القديم وإضافة vault_id
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('vault_type');
            $table->foreignId('vault_id')->nullable()->after('payment_method')->constrained('vaults')->onDelete('set null');
        });

        Schema::table('revenues', function (Blueprint $table) {
            $table->dropColumn('vault_type');
            $table->foreignId('vault_id')->nullable()->after('payment_method')->constrained('vaults')->onDelete('set null');
        });

        Schema::table('student_payments', function (Blueprint $table) {
            $table->dropColumn('vault_type');
            $table->foreignId('vault_id')->nullable()->after('payment_method')->constrained('vaults')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['vault_id']);
            $table->dropColumn('vault_id');
            $table->enum('vault_type', ['vodafone', 'instapay', 'cash'])->default('cash')->after('payment_method');
        });

        Schema::table('revenues', function (Blueprint $table) {
            $table->dropForeign(['vault_id']);
            $table->dropColumn('vault_id');
            $table->enum('vault_type', ['vodafone', 'instapay', 'cash'])->default('cash')->after('payment_method');
        });

        Schema::table('student_payments', function (Blueprint $table) {
            $table->dropForeign(['vault_id']);
            $table->dropColumn('vault_id');
            $table->enum('vault_type', ['vodafone', 'instapay', 'cash'])->default('cash')->after('payment_method');
        });
    }
};
