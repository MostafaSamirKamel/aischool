<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nationalities', function (Blueprint $table) {
            // Add tenant_id as nullable first
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
        });

        // Update existing records with first tenant (or default to 1)
        DB::table('nationalities')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => 1]);

        // Make tenant_id NOT NULL
        Schema::table('nationalities', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            // Add unique constraint on tenant_id + code
            $table->unique(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nationalities', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'code']);
            $table->dropColumn('tenant_id');
        });
    }
};
