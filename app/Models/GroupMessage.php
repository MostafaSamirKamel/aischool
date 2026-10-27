<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToBranch;

class GroupMessage extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'group_id',
        'sender_id',
        'sender_type',
        'subject',
        'message',
        'type',
        'priority',
        'attachments',
        'send_notification',
        'send_email',
        'recipients_count',
        'read_count',
        'scheduled_at',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'attachments' => 'array',
        'send_notification' => 'boolean',
        'send_email' => 'boolean',
        'recipients_count' => 'integer',
        'read_count' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Relations
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(GroupMessageRead::class, 'message_id');
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeUnread($query, $studentId)
    {
        return $query->whereDoesntHave('reads', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
    }

    // Helper methods
    public function send(): bool
    {
        if ($this->status === 'sent') {
            return false;
        }

        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipients_count' => $this->group->enrollments()->active()->count(),
        ]);

        // Send notifications if enabled
        if ($this->send_notification) {
            $this->sendNotifications();
        }

        return true;
    }

    public function schedule($dateTime): bool
    {
        $this->update([
            'status' => 'scheduled',
            'scheduled_at' => $dateTime,
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if ($this->status === 'sent') {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
        ]);

        return true;
    }

    public function markAsRead($studentId): void
    {
        GroupMessageRead::firstOrCreate([
            'message_id' => $this->id,
            'student_id' => $studentId,
        ], [
            'read_at' => now(),
        ]);

        $this->update([
            'read_count' => $this->reads()->count(),
        ]);
    }

    public function isReadBy($studentId): bool
    {
        return $this->reads()->where('student_id', $studentId)->exists();
    }

    public function getReadPercentage(): float
    {
        if ($this->recipients_count === 0) {
            return 0;
        }

        return round(($this->read_count / $this->recipients_count) * 100, 2);
    }

    protected function sendNotifications(): void
    {
        $students = $this->group->enrollments()->active()->get()->pluck('student');

        foreach ($students as $student) {
            // Send notification (implement notification system later)
            // Notification::send($student->user, new NewGroupMessage($this));
        }
    }

    // Static methods
    public static function getUnreadCountForStudent($studentId, $groupId = null): int
    {
        $query = static::sent();

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        return $query->unread($studentId)->count();
    }
}
