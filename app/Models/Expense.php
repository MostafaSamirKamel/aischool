<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'expense_code',
        'expense_category_id',
        'vault_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'invoice_number',
        'vendor_name',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_code)) {
                $expense->expense_code = self::generateExpenseCode();
            }
            if (empty($expense->created_by)) {
                $expense->created_by = auth()->id();
            }
            // tenant_id will be set automatically by BelongsToBranch trait
        });

        static::saving(function ($expense) {
            if ($expense->status === 'paid' && (float) $expense->amount > 0) {
                if (empty($expense->vault_id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المنصرف منها إلزامية لتسجيل أي مصروف مدفوع.',
                    ]);
                }

                $vault = Vault::find($expense->vault_id);
                if (!$vault || !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير صالحة أو غير نشطة.',
                    ]);
                }

                if (!empty($expense->tenant_id) && !empty($vault->tenant_id) && (int) $expense->tenant_id !== (int) $vault->tenant_id) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع المصروف.');
                }

                $originalAmount = 0.0;
                if ($expense->exists && $expense->getOriginal('vault_id') == $expense->vault_id && $expense->getOriginal('status') === 'paid') {
                    $originalAmount = (float) $expense->getOriginal('amount');
                }
                $vault->assertSufficientBalance((float) $expense->amount, $originalAmount);

                // Auto-set payment_method from vault type for audit consistency
                if (empty($expense->payment_method) || $expense->payment_method === 'cash') {
                    $expense->payment_method = match ($vault->type) {
                        'cash' => 'cash',
                        'vodafone', 'instapay', 'mobile_wallet', 'wallet' => 'mobile_wallet',
                        'bank' => 'bank_transfer',
                        default => 'cash',
                    };
                }
            } elseif ($expense->vault_id && (float) $expense->amount > 0) {
                // If a vault was optionally attached to an approved/pending expense, verify it
                $vault = Vault::find($expense->vault_id);
                if ($vault && !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير نشطة.',
                    ]);
                }
            }
        });
    }

    public static function generateExpenseCode(): string
    {
        $latestRecord = self::withoutGlobalScopes()
            ->withTrashed()
            ->whereNotNull('expense_code')
            ->where('expense_code', 'like', 'EXP-%')
            ->orderBy('expense_code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestRecord && preg_match('/EXP-(\d+)/', $latestRecord->expense_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $maxId = (int) self::withoutGlobalScopes()->withTrashed()->max('id');
        if ($maxId >= $nextNumber) {
            $nextNumber = $maxId + 1;
        }

        do {
            $code = 'EXP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $exists = self::withoutGlobalScopes()
                ->withTrashed()
                ->where('expense_code', $code)
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
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
