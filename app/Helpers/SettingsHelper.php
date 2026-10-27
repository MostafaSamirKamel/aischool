<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Models\Tenant;

class SettingsHelper
{
    /**
     * جلب إعدادات حسب tenant
     * إذا كان المستخدم super admin، يجلب الإعدادات العامة (بدون tenant)
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getSettings();
        return $settings?->$key ?? $default;
    }

    /**
     * جلب كائن الإعدادات الكامل
     */
    public static function getSettings(): ?Setting
    {
        $tenantId = self::getTenantId();

        if ($tenantId) {
            // جلب إعدادات الـ tenant
            return Setting::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();
        } else {
            // جلب الإعدادات العامة (للسوبر أدمن)
            return Setting::withoutGlobalScopes()->whereNull('tenant_id')->first();
        }
    }

    /**
     * تحديث إعداد معين
     */
    public static function set(string $key, $value): void
    {
        $settings = self::getSettings();
        
        if (!$settings) {
            $settings = Setting::create([
                'tenant_id' => self::getTenantId(),
            ]);
        }

        $settings->update([$key => $value]);
    }

    /**
     * جلب عنوان الموقع
     */
    public static function getSiteTitle(): string
    {
        return self::get('company_name', config('app.name', 'AI School'));
    }

    /**
     * جلب شعار الموقع
     */
    public static function getSiteLogo(): ?string
    {
        return self::get('company_logo');
    }

    /**
     * جلب البريد الإلكتروني
     */
    public static function getSiteEmail(): ?string
    {
        return self::get('company_email');
    }

    /**
     * جلب رقم الهاتف
     */
    public static function getSitePhone(): ?string
    {
        return self::get('company_phone');
    }

    /**
     * جلب رقم الواتساب
     */
    public static function getSiteWhatsapp(): ?string
    {
        return self::get('company_whatsapp');
    }

    /**
     * الحصول على tenant_id الحالي
     */
    protected static function getTenantId(): ?int
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        // إذا كان super admin أو بدون tenant_id، إرجاع null
        if ($user->is_super_admin || is_null($user->tenant_id)) {
            return null;
        }

        return $user->tenant_id;
    }

    /**
     * جلب جميع الإعدادات كمصفوفة
     */
    public static function all(): array
    {
        $settings = self::getSettings();
        return $settings ? $settings->toArray() : [];
    }
}