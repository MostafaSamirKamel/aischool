<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToBranch;

class StudentMessage extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'sender_id',
        'sender_type',
        'subject',
        'message',
        'type',
        'priority',
        'attachments',
        'is_read',
        'read_at',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Relations
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Helper methods
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function send(): bool
    {
        if ($this->status === 'sent') {
            return false;
        }

        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return true;
    }

    // Static methods
    public static function getUnreadCountForStudent($studentId): int
    {
        return static::sent()
            ->forStudent($studentId)
            ->unread()
            ->count();
    }
}
