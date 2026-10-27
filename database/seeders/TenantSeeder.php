<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['slug' => 'main'],
            [
                'name' => 'الفرع الرئيسي - القاهرة',
                'domain' => 'main.aischool.com',
                'is_active' => true,
            ]
        );

        Tenant::firstOrCreate(
            ['slug' => 'damanhour'],
            [
                'name' => 'فرع دمنهور',
                'domain' => 'damanhour.aischool.com',
                'is_active' => true,
            ]
        );
    }
}
