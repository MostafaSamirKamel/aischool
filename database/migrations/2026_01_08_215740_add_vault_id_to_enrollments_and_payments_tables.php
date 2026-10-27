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
        // Add vault_id to enrollments table
        if (!Schema::hasColumn('enrollments', 'vault_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->unsignedBigInteger('vault_id')->nullable()->after('paid_amount');
            });
        }

        // Add vault_id to payments table
        if (!Schema::hasColumn('payments', 'vault_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('vault_id')->nullable()->after('amount');
            });
        }

       
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'vault_id')) {
                $table->dropForeign(['vault_id']);
                $table->dropColumn('vault_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'vault_id')) {
                $table->dropForeign(['vault_id']);
                $table->dropColumn('vault_id');
            }
        });
    }
};
