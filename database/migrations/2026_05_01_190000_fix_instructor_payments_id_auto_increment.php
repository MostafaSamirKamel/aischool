<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('instructor_payments') || DB::getDriverName() === 'sqlite') {
            return;
        }

        // Repair broken schemas where id lost AUTO_INCREMENT.
        DB::statement('ALTER TABLE instructor_payments MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep as no-op to avoid reverting to an invalid schema.
    }
};
