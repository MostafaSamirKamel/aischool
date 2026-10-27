<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPaymentItem extends Model
{
    protected $fillable = [
        'student_payment_id',
        'enrollment_id',
        'course_id',
        'course_price',
        'amount_paid',
        'notes',
    ];

    protected $casts = [
        'course_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    // Relationships
    public function studentPayment()
    {
        return $this->belongsTo(StudentPayment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
