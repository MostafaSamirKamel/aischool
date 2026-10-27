<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            ['code' => 'EG', 'name' => 'مصري'],
            ['code' => 'SA', 'name' => 'سعودي'],
            ['code' => 'LY', 'name' => 'ليبي'],
            ['code' => 'SY', 'name' => 'سوري'],
            ['code' => 'SD', 'name' => 'سوداني'],
            ['code' => 'AE', 'name' => 'إماراتي'],
            ['code' => 'KW', 'name' => 'كويتي'],
            ['code' => 'QA', 'name' => 'قطري'],
            ['code' => 'BH', 'name' => 'بحريني'],
            ['code' => 'OM', 'name' => 'عماني'],
            ['code' => 'YE', 'name' => 'يمني'],
            ['code' => 'JO', 'name' => 'أردني'],
            ['code' => 'PS', 'name' => 'فلسطيني'],
            ['code' => 'LB', 'name' => 'لبناني'],
            ['code' => 'IQ', 'name' => 'عراقي'],
            ['code' => 'TN', 'name' => 'تونسي'],
            ['code' => 'DZ', 'name' => 'جزائري'],
            ['code' => 'MA', 'name' => 'مغربي'],
        ];

        foreach ($nationalities as $nationality) {
            \App\Models\Nationality::firstOrCreate(
                ['code' => $nationality['code']],
                array_merge($nationality, ['tenant_id' => 1])
            );
        }

        $this->command->info('✅ تم إنشاء الجنسيات بنجاح!');
    }
}
