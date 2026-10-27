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
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('governorate_id')->nullable()->after('address')->constrained('governorates')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->after('governorate_id')->constrained('districts')->onDelete('set null');
            $table->text('detailed_address')->nullable()->after('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['governorate_id', 'district_id', 'detailed_address']);
        });
    }
};
