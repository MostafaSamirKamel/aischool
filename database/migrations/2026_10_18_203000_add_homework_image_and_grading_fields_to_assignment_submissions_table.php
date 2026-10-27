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
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_submissions', 'images')) {
                $table->text('images')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('assignment_submissions', 'published_at')) {
                $table->dateTime('published_at')->nullable()->after('graded_at');
            }
            if (!Schema::hasColumn('assignment_submissions', 'graded_by')) {
                $table->unsignedBigInteger('graded_by')->nullable()->after('published_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('assignment_submissions', 'images')) {
                $columnsToDrop[] = 'images';
            }
            if (Schema::hasColumn('assignment_submissions', 'published_at')) {
                $columnsToDrop[] = 'published_at';
            }
            if (Schema::hasColumn('assignment_submissions', 'graded_by')) {
                $columnsToDrop[] = 'graded_by';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
