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
        // حذف الإعدادات بدون tenant_id (الإعدادات اليتيمة)
        \DB::table('settings')->whereNull('tenant_id')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا يمكن استرجاع البيانات المحذوفة
    }
};
