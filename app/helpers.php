<?php

if (!function_exists('setting')) {
    /**
     * جلب إعداد من جدول settings حسب الـ tenant
     * 
     * @param string $key مفتاح الإعداد
     * @param mixed $default القيمة الافتراضية
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return \App\Helpers\SettingsHelper::get($key, $default);
    }
}

if (!function_exists('site_title')) {
    /**
     * جلب عنوان الموقع
     * 
     * @return string
     */
    function site_title(): string
    {
        return \App\Helpers\SettingsHelper::getSiteTitle();
    }
}

if (!function_exists('site_logo')) {
    /**
     * جلب شعار الموقع
     * 
     * @return string|null
     */
    function site_logo(): ?string
    {
        return \App\Helpers\SettingsHelper::getSiteLogo();
    }
}

if (!function_exists('site_email')) {
    /**
     * جلب البريد الإلكتروني للموقع
     * 
     * @return string|null
     */
    function site_email(): ?string
    {
        return \App\Helpers\SettingsHelper::getSiteEmail();
    }
}

if (!function_exists('site_phone')) {
    /**
     * جلب رقم هاتف الموقع
     * 
     * @return string|null
     */
    function site_phone(): ?string
    {
        return \App\Helpers\SettingsHelper::getSitePhone();
    }
}

if (!function_exists('site_whatsapp')) {
    /**
     * جلب رقم واتساب الموقع
     * 
     * @return string|null
     */
    function site_whatsapp(): ?string
    {
        return \App\Helpers\SettingsHelper::getSiteWhatsapp();
    }
}