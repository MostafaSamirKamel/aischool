<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SmsConfig extends Model
{
    

    protected $table = 'sms_configs';

    protected $fillable = [
        'tenant_id',
        'default_driver',
        'queue',
        'log_messages',
        'rate_limit_per_minute',
        'rate_limit_per_hour',
        
        // Twilio
        'twilio_sid',
        'twilio_token',
        'twilio_from',
        
        // Vonage
        'vonage_key',
        'vonage_secret',
        'vonage_from',
        
        // Vodafone
        'vodafone_username',
        'vodafone_password',
        'vodafone_sender',
    ];

    protected $casts = [
        'queue' => 'boolean',
        'log_messages' => 'boolean',
        'rate_limit_per_minute' => 'integer',
        'rate_limit_per_hour' => 'integer',
    ];

    /**
     * Get activity log options
     */
   

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        parent::booted();

        // Prevent deletion of settings
        static::deleting(function ($config) {
            throw new \Exception('لا يمكن حذف إعدادات SMS. يمكنك تعديلها فقط.');
        });
    }
}
