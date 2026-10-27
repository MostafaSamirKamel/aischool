<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentPayment extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $table = 'student_payments';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'vault_id',
        'payment_number',
        'payment_date',
        'total_amount',
        'discount',
        'net_amount',
        'payment_method',
        'status',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function vault()
    {
        return $this->belongsTo(Vault::class);
    }

    public function items()
    {
        return $this->hasMany(StudentPaymentItem::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
