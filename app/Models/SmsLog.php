<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'phone',
        'message',
        'driver',
        'status',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Scope for sent messages
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for today's messages
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sent_at', today());
    }

    /**
     * Scope for messages in date range
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('sent_at', [$from, $to]);
    }
}
