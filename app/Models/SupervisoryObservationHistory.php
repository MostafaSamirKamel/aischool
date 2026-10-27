<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisoryObservationHistory extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'observation_id',
        'actor_id',
        'action',
        'previous_status',
        'new_status',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(SupervisoryObservation::class, 'observation_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'إنشاء الملاحظة الإشرافية',
            'instructor_responded' => 'رد المعلم',
            'submitted_for_review' => 'تقديم المعالجة لمراجعة المشرف',
            'returned_for_correction' => 'إعادة الملاحظة للاستكمال مع ذكر السبب',
            'approved_completed' => 'اعتماد المشرف لإغلاق الملاحظة',
            'deadline_changed' => 'تعديل موعد المراجعة المتوقع',
            'evidence_updated' => 'إرفاق شواهد أو مستندات',
            default => 'تحديث في السجل',
        };
    }
}
