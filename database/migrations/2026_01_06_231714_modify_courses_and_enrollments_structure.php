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
        // // Remove group_id from enrollments using raw SQL to avoid constraint issues
        // DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // DB::statement('ALTER TABLE enrollments DROP INDEX enrollments_student_id_group_id_unique');
        // DB::statement('ALTER TABLE enrollments DROP INDEX enrollments_group_id_foreign');
        // DB::statement('ALTER TABLE enrollments DROP COLUMN group_id');
        // DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Track_id in courses becomes nullable (courses can exist without tracks)
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('track_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore track_id to not nullable in courses
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('track_id')->nullable(false)->change();
        });
        
        // Restore group_id in enrollments
        // Schema::table('enrollments', function (Blueprint $table) {
        //     $table->foreignId('group_id')->after('student_id')->constrained()->onDelete('cascade');
        //     $table->unique(['student_id', 'group_id']);
        // });
    }
};
