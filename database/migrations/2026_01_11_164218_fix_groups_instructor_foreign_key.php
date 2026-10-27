<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $existingForeignKey = $this->getForeignKeyName('groups', 'instructor_id');
        if ($existingForeignKey) {
            DB::statement("ALTER TABLE `groups` DROP FOREIGN KEY `{$existingForeignKey}`");
        }

        DB::table('groups')
            ->whereNotNull('instructor_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('instructors')
                    ->whereColumn('instructors.id', 'groups.instructor_id');
            })
            ->update(['instructor_id' => null]);

        Schema::table('groups', function (Blueprint $table) {
            // Add the corrected foreign key constraint pointing to instructors table
            $table->foreign('instructor_id')
                ->references('id')
                ->on('instructors')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $existingForeignKey = $this->getForeignKeyName('groups', 'instructor_id');
        if ($existingForeignKey) {
            DB::statement("ALTER TABLE `groups` DROP FOREIGN KEY `{$existingForeignKey}`");
        }

        Schema::table('groups', function (Blueprint $table) {
            // Restore the old foreign key pointing to users table
            $table->foreign('instructor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    private function getForeignKeyName(string $table, string $column): ?string
    {
        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$table, $column]
        );

        return $result?->name;
    }
};
