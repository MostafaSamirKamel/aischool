<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إعدادات عامة للسوبر أدمن (بدون tenant)
        Setting::create([
            'tenant_id' => null,
            'company_name' => 'AI School - المنصة الرئيسية',
            'company_address' => 'القاهرة، مصر',
            'company_phone' => '+20 123 456 7890',
            'company_whatsapp' => '+20 123 456 7890',
            'company_email' => 'info@aischool.com',
            'company_website' => 'https://aischool.com',
            'about_company' => 'منصة تعليمية متكاملة لإدارة المدارس والأكاديميات',
            'currency' => 'EGP',
            'timezone' => 'Africa/Cairo',
            'seo_title' => 'AI School - نظام إدارة المدارس',
            'seo_description' => 'نظام متكامل لإدارة المدارس والأكاديميات التعليمية',
            'seo_keywords' => 'مدرسة، تعليم، إدارة، نظام تعليمي',
        ]);

        // إعدادات لكل فرع
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            Setting::create([
                'tenant_id' => $tenant->id,
                'company_name' => $tenant->name . ' - AI School',
                'company_address' => 'عنوان ' . $tenant->name,
                'company_phone' => '+20 100 000 000' . $tenant->id,
                'company_whatsapp' => '+20 100 000 000' . $tenant->id,
                'company_email' => strtolower(str_replace(' ', '', $tenant->name)) . '@aischool.com',
                'company_website' => 'https://aischool.com/' . strtolower(str_replace(' ', '-', $tenant->name)),
                'about_company' => 'فرع ' . $tenant->name . ' - منصة تعليمية متكاملة',
                'currency' => 'EGP',
                'timezone' => 'Africa/Cairo',
                'seo_title' => $tenant->name . ' - AI School',
                'seo_description' => 'فرع ' . $tenant->name . ' التعليمي',
                'seo_keywords' => 'مدرسة، تعليم، ' . $tenant->name,
            ]);
        }
    }
}
