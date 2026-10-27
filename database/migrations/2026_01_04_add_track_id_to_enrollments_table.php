<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('track_id')->nullable()->after('group_id')->constrained()->nullOnDelete();
            $table->index('track_id');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropIndex(['track_id']);
            $table->dropColumn('track_id');
        });
    }
};
