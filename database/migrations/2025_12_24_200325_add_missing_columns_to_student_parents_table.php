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
        Schema::table('student_parents', function (Blueprint $table) {
            // إضافة الأعمدة المفقودة فقط
            if (!Schema::hasColumn('student_parents', 'notes')) {
                $table->text('notes')->nullable()->after('receive_notifications');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_parents', function (Blueprint $table) {
            if (Schema::hasColumn('student_parents', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
