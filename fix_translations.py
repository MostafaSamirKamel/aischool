#!/usr/bin/env python3
import re
import os

files = [
    "app/Filament/Admin/Resources/Settings/Schemas/SettingForm.php",
]

translations = {
    "'filament/admin/setting_resource._section_header_1'": "'معلومات الشركة'",
    "'filament/admin/setting_resource._section_header_2'": "'الموقع الجغرافي'",
    "'filament/admin/setting_resource._section_header_3'": "'وسائل التواصل الاجتماعية'",
    "'filament/admin/setting_resource._section_header_4'": "'إعدادات SEO'",
    "'filament/admin/setting_resource._section_header_5'": "'إعدادات البريد الإلكتروني'",
    "'filament/admin/setting_resource._section_header_6'": "'الإعدادات العامة'",
    "'filament/admin/setting_resource.company_name'": "'اسم الشركة'",
    "'filament/admin/setting_resource.company_logo'": "'شعار الشركة'",
    "'filament/admin/setting_resource.company_email'": "'بريد الشركة الإلكتروني'",
    "'filament/admin/setting_resource.company_phone'": "'هاتف الشركة'",
    "'filament/admin/setting_resource.company_whatsapp'": "'واتساب الشركة'",
    "'filament/admin/setting_resource.company_website'": "'موقع الشركة الإلكتروني'",
    "'filament/admin/setting_resource.company_address'": "'عنوان الشركة'",
    "'filament/admin/setting_resource.about_company'": "'نبذة عن الشركة'",
    "'filament/admin/setting_resource.latitude'": "'خط العرض'",
    "'filament/admin/setting_resource.longitude'": "'خط الطول'",
    "'filament/admin/setting_resource.social_facebook'": "'Facebook'",
    "'filament/admin/setting_resource.social_instagram'": "'Instagram'",
    "'filament/admin/setting_resource.social_twitter'": "'Twitter'",
    "'filament/admin/setting_resource.social_linkedin'": "'LinkedIn'",
    "'filament/admin/setting_resource.social_telegram'": "'Telegram'",
    "'filament/admin/setting_resource.social_whatsapp'": "'WhatsApp'",
    "'filament/admin/setting_resource.social_tiktok'": "'TikTok'",
    "'filament/admin/setting_resource.social_youtube'": "'YouTube'",
    "'filament/admin/setting_resource.seo_title'": "'عنوان SEO'",
    "'filament/admin/setting_resource.seo_description'": "'وصف SEO'",
    "'filament/admin/setting_resource.seo_keywords'": "'كلمات مفتاح SEO'",
    "'filament/admin/setting_resource.google_analytics_id'": "'معرف Google Analytics'",
    "'filament/admin/setting_resource.google_search_console'": "'Google Search Console'",
    "'filament/admin/setting_resource.mail_driver'": "'برنامج البريد'",
    "'filament/admin/setting_resource.mail_host'": "'خادم البريد'",
    "'filament/admin/setting_resource.mail_port'": "'منفذ البريد'",
    "'filament/admin/setting_resource.mail_username'": "'اسم مستخدم البريد'",
    "'filament/admin/setting_resource.mail_password'": "'كلمة مرور البريد'",
    "'filament/admin/setting_resource.mail_encryption'": "'تشفير البريد'",
    "'filament/admin/setting_resource.mail_from_address'": "'عنوان المرسل'",
    "'filament/admin/setting_resource.mail_from_name'": "'اسم المرسل'",
    "'filament/admin/setting_resource.currency'": "'العملة'",
    "'filament/admin/setting_resource.timezone'": "'المنطقة الزمنية'",
    "'filament/admin/setting_resource.maintenance_mode'": "'وضع الصيانة'",
}

for file_path in files:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    for old, new in translations.items():
        content = content.replace(f"__({old})", new)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"✓ Fixed {file_path}")

print("✅ Done!")
