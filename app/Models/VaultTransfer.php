<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaultTransfer extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'from_vault_id',
        'to_vault_id',
        'amount',
        'transfer_date',
        'transfer_code',
        'status',
        'notes',
        'cancellation_reason',
        'transferred_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            if (auth()->check()) {
                $transfer->transferred_by = auth()->id();
            }
            
            // توليد كود التحويل تلقائياً
            if (empty($transfer->transfer_code)) {
                $transfer->transfer_code = self::generateTransferCode();
            }
        });

        // عند إنشاء أو اعتماد التحويل كمكتمل، نتحقق من صحة الخزائن وكفاية الرصيد وتحديث بيانات الاعتماد
        static::saving(function ($transfer) {
            if (empty($transfer->from_vault_id)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'from_vault_id' => 'الخزنة المحول منها إلزامية.',
                ]);
            }

            if (empty($transfer->to_vault_id)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'to_vault_id' => 'الخزنة المحول إليها إلزامية.',
                ]);
            }

            if ((int) $transfer->from_vault_id === (int) $transfer->to_vault_id) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'to_vault_id' => 'لا يمكن التحويل من الخزنة إلى نفسها.',
                ]);
            }

            $fromVault = Vault::find($transfer->from_vault_id);
            $toVault = Vault::find($transfer->to_vault_id);

            if (!$fromVault || !$fromVault->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'from_vault_id' => 'الخزنة المحول منها غير موجودة أو غير نشطة.',
                ]);
            }

            if (!$toVault || !$toVault->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'to_vault_id' => 'الخزنة المحول إليها غير موجودة أو غير نشطة.',
                ]);
            }

            if (!empty($fromVault->tenant_id) && !empty($toVault->tenant_id) && (int) $fromVault->tenant_id !== (int) $toVault->tenant_id) {
                throw new \Illuminate\Auth\Access\AuthorizationException('لا يمكن التحويل المالي بين خزائن فروع مختلفة.');
            }

            if ($transfer->status === 'completed' && (float) $transfer->amount > 0) {
                $originalAmount = 0.0;
                if ($transfer->exists && $transfer->getOriginal('from_vault_id') == $transfer->from_vault_id && $transfer->getOriginal('status') === 'completed') {
                    $originalAmount = (float) $transfer->getOriginal('amount');
                }
                $fromBalance = (float) $fromVault->getCurrentBalance() + $originalAmount;
                if ($fromBalance < (float) $transfer->amount) {
                    $isUnauthorized = auth()->check() && !auth()->user()->is_super_admin && !auth()->user()->can('view_vault_balances');
                    if ($isUnauthorized) {
                        throw new \Exception('لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية.');
                    }
                    throw new \Exception('لا يمكن إتمام التحويل: رصيد الخزنة المحول منها غير كافٍ. الرصيد الحالي: ' . number_format($fromBalance, 2) . ' ج.م');
                }

                if (auth()->check() && empty($transfer->approved_by)) {
                    $transfer->approved_by = auth()->id();
                    $transfer->approved_at = now();
                }
            }
        });
    }

    /**
     * Generate unique transfer code
     */
    public static function generateTransferCode(): string
    {
        $prefix = 'VT-';
        $date = now()->format('ymd');
        $fullPrefix = $prefix . $date . '-';

        $lastTransfer = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('transfer_code', 'like', $fullPrefix . '%')
            ->orderBy('transfer_code', 'desc')
            ->first();

        $number = 1;
        if ($lastTransfer) {
            $lastNumber = (int) substr($lastTransfer->transfer_code, -4);
            $number = $lastNumber + 1;
        }

        do {
            $code = $fullPrefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->withTrashed()
                ->where('transfer_code', $code)
                ->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }

    // Relationships
    public function fromVault(): BelongsTo
    {
        return $this->belongsTo(Vault::class, 'from_vault_id');
    }

    public function toVault(): BelongsTo
    {
        return $this->belongsTo(Vault::class, 'to_vault_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'pending';
    }
}
