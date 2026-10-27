<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSetting extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'default_period',
        'alert_threshold_debt',
        'alert_threshold_low_score_percentage',
        'alert_homework_overdue_hours',
        'pdf_header_text',
        'pdf_footer_text',
        'custom_settings',
        'updated_by',
    ];

    protected $casts = [
        'alert_threshold_debt' => 'decimal:2',
        'alert_threshold_low_score_percentage' => 'decimal:2',
        'alert_homework_overdue_hours' => 'integer',
        'custom_settings' => 'array',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
