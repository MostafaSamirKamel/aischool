<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;


class Setting extends Model
{
    use BelongsToBranch;

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        parent::booted();

        // Prevent deletion of settings
        static::deleting(function ($setting) {
            throw new \Exception('لا يمكن حذف سجل الإعدادات. يمكنك تعديله فقط.');
        });
    }
    protected $fillable = [
        'tenant_id',
        // معلومات الشركة
        'company_name',
        'company_address',
        'company_phone',
        'company_whatsapp',
        'company_email',
        'company_logo',
        'company_website',
        
        // الموقع الجغرافي
        'latitude',
        'longitude',
        
        // صفحات التواصل الاجتماعي
        'social_facebook',
        'social_whatsapp',
        'social_telegram',
        'social_instagram',
        'social_linkedin',
        'social_twitter',
        'social_tiktok',
        'social_youtube',
        
        // إعدادات SEO
        'seo_title',
        'seo_description',
        'seo_keywords',
        'google_analytics_id',
        'google_search_console',
        
        // إعدادات البريد الإلكتروني
        'mail_from_address',
        'mail_from_name',
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        
        // إعدادات إضافية
        'about_company',
        'currency',
        'timezone',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'mail_port' => 'integer',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }



}
