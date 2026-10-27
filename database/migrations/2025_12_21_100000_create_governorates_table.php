<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();
        });

        // إدراج أكواد المحافظات المصرية
        DB::table('governorates')->insert([
            ['code' => '01', 'name_ar' => 'القاهرة', 'name_en' => 'Cairo'],
            ['code' => '02', 'name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria'],
            ['code' => '03', 'name_ar' => 'بورسعيد', 'name_en' => 'Port Said'],
            ['code' => '04', 'name_ar' => 'السويس', 'name_en' => 'Suez'],
            ['code' => '11', 'name_ar' => 'دمياط', 'name_en' => 'Damietta'],
            ['code' => '12', 'name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia'],
            ['code' => '13', 'name_ar' => 'الشرقية', 'name_en' => 'Sharqia'],
            ['code' => '14', 'name_ar' => 'القليوبية', 'name_en' => 'Qalyubia'],
            ['code' => '15', 'name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh'],
            ['code' => '16', 'name_ar' => 'الغربية', 'name_en' => 'Gharbia'],
            ['code' => '17', 'name_ar' => 'المنوفية', 'name_en' => 'Monufia'],
            ['code' => '18', 'name_ar' => 'البحيرة', 'name_en' => 'Beheira'],
            ['code' => '19', 'name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia'],
            ['code' => '21', 'name_ar' => 'الجيزة', 'name_en' => 'Giza'],
            ['code' => '22', 'name_ar' => 'بني سويف', 'name_en' => 'Beni Suef'],
            ['code' => '23', 'name_ar' => 'الفيوم', 'name_en' => 'Fayoum'],
            ['code' => '24', 'name_ar' => 'المنيا', 'name_en' => 'Minya'],
            ['code' => '25', 'name_ar' => 'أسيوط', 'name_en' => 'Asyut'],
            ['code' => '26', 'name_ar' => 'سوهاج', 'name_en' => 'Sohag'],
            ['code' => '27', 'name_ar' => 'قنا', 'name_en' => 'Qena'],
            ['code' => '28', 'name_ar' => 'أسوان', 'name_en' => 'Aswan'],
            ['code' => '29', 'name_ar' => 'الأقصر', 'name_en' => 'Luxor'],
            ['code' => '31', 'name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea'],
            ['code' => '32', 'name_ar' => 'الوادي الجديد', 'name_en' => 'New Valley'],
            ['code' => '33', 'name_ar' => 'مطروح', 'name_en' => 'Matrouh'],
            ['code' => '34', 'name_ar' => 'شمال سيناء', 'name_en' => 'North Sinai'],
            ['code' => '35', 'name_ar' => 'جنوب سيناء', 'name_en' => 'South Sinai'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};