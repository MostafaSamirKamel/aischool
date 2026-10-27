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
        Schema::table('supervisors', function (Blueprint $table) {
            // تغيير nationality من string إلى foreign key
            $table->unsignedBigInteger('nationality')->nullable()->change();
            $table->foreign('nationality')->references('id')->on('nationalities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            // إرجاع nationality إلى string
            $table->dropForeign(['nationality']);
            $table->string('nationality')->default('Saudi')->change();
        });
    }
};