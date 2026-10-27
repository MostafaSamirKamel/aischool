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
        Schema::table('enrollment_courses', function (Blueprint $table) {
            // Add status column: inactive, active, completed
            $table->enum('status', ['inactive', 'active', 'completed'])->default('inactive')->after('course_price');
            
            // Add current session counter (0-12)
            $table->integer('current_session')->default(0)->after('status');
            
            // Add group_id to track which group the student is in for this course
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete()->after('current_session');
            
            // Add session count (total expected sessions)
            $table->integer('total_sessions')->default(12)->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_courses', function (Blueprint $table) {
            $table->dropColumn(['status', 'current_session', 'group_id', 'total_sessions']);
        });
    }
};
