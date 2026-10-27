<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->tinyInteger('commitment_score')->nullable()->after('check_out_time')->comment('درجة الالتزام من 0-10');
            $table->tinyInteger('focus_score')->nullable()->after('commitment_score')->comment('درجة التركيز من 0-10');
            $table->tinyInteger('participation_score')->nullable()->after('focus_score')->comment('درجة المشاركة من 0-10');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['commitment_score', 'focus_score', 'participation_score']);
        });
    }
};
