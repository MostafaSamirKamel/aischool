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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Modify the enum column to include all relationship types
        DB::statement("ALTER TABLE `student_parents` MODIFY `relation` ENUM('father', 'mother', 'guardian', 'brother', 'sister', 'uncle', 'aunt', 'other') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert back to original enum values
        DB::statement("ALTER TABLE `student_parents` MODIFY `relation` ENUM('father', 'mother', 'guardian', 'other') NOT NULL");
    }
};
