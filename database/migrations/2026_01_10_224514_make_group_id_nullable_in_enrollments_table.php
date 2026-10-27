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

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop the unique constraint if exists
        try {
            DB::statement('ALTER TABLE enrollments DROP INDEX enrollments_student_id_group_id_unique');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }
        
        // Drop the foreign key if exists
        try {
            DB::statement('ALTER TABLE enrollments DROP FOREIGN KEY enrollments_group_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }
        
        // Make group_id nullable
        DB::statement('ALTER TABLE enrollments MODIFY group_id BIGINT UNSIGNED NULL');
        
        // Re-add the foreign key constraint with SET NULL on delete
        DB::statement('ALTER TABLE enrollments ADD CONSTRAINT enrollments_group_id_foreign FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop the foreign key
        try {
            DB::statement('ALTER TABLE enrollments DROP FOREIGN KEY enrollments_group_id_foreign');
        } catch (\Exception $e) {
            // Continue
        }
        
        // Make group_id not nullable again
        DB::statement('ALTER TABLE enrollments MODIFY group_id BIGINT UNSIGNED NOT NULL');
        
        // Re-add the foreign key and unique constraint
        DB::statement('ALTER TABLE enrollments ADD CONSTRAINT enrollments_group_id_foreign FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE enrollments ADD UNIQUE enrollments_student_id_group_id_unique (student_id, group_id)');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
