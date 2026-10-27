<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenue extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'revenue_code',
        'revenue_category_id',
        'vault_id',
        'title',
        'description',
        'amount',
        'revenue_date',
        'payment_method',
        'invoice_number',
        'customer_name',
        'payment_id',
        'notes',
        'status',
        'received_by',
        'received_at',
        'created_by',
    ];

    protected $casts = [
        'revenue_date' => 'date',
        'received_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($revenue) {
            if (empty($revenue->revenue_code)) {
                $revenue->revenue_code = self::generateRevenueCode();
            }
            if (empty($revenue->created_by)) {
                $revenue->created_by = auth()->id();
            }
            if (empty($revenue->payment_method)) {
                $revenue->payment_method = 'cash';
            }
            // tenant_id will be set automatically by BelongsToBranch trait
        });

        static::saving(function ($revenue) {
            if ($revenue->status === 'received' && (float) $revenue->amount > 0) {
                if (empty($revenue->vault_id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المستلمة إلزامية لتسجيل أي إيراد مستلم.',
                    ]);
                }

                $vault = Vault::find($revenue->vault_id);
                if (!$vault || !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير صالحة أو غير نشطة.',
                    ]);
                }

                if (!empty($revenue->tenant_id) && !empty($vault->tenant_id) && (int) $revenue->tenant_id !== (int) $vault->tenant_id) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع الإيراد.');
                }

                // Sync payment_method with vault type for DB/audit consistency
                $revenue->payment_method = match ($vault->type) {
                    'cash' => 'cash',
                    'vodafone', 'instapay', 'mobile_wallet', 'wallet' => 'online',
                    'bank' => 'bank_transfer',
                    default => 'cash',
                };
            }
        });
    }

    public static function generateRevenueCode(): string
    {
        $latestRecord = self::withoutGlobalScopes()
            ->withTrashed()
            ->whereNotNull('revenue_code')
            ->where('revenue_code', 'like', 'REV-%')
            ->orderBy('revenue_code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestRecord && preg_match('/REV-(\d+)/', $latestRecord->revenue_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $maxId = (int) self::withoutGlobalScopes()->withTrashed()->max('id');
        if ($maxId >= $nextNumber) {
            $nextNumber = $maxId + 1;
        }

        do {
            $code = 'REV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $exists = self::withoutGlobalScopes()
                ->withTrashed()
                ->where('revenue_code', $code)
                ->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $code;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class, 'revenue_category_id');
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
