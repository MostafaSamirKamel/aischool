<?php

namespace App\Filament\Instructor\Resources\GroupMessageResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Instructor\Resources\GroupMessageResource\GroupMessageResource;

class CreateGroupMessage extends CreateRecord
{
    protected static string $resource = GroupMessageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sender_id'] = auth()->id();
        $data['sender_type'] = 'instructor';
        $data['tenant_id'] = auth()->user()->tenant_id;
        
        // إذا لم يتم تحديد موعد للجدولة، يتم الإرسال فوراً
        if (empty($data['scheduled_at'])) {
            $data['status'] = 'sent';
            $data['sent_at'] = now();
            
            // حساب عدد المستلمين من المجموعة
            if (isset($data['group_id'])) {
                $group = \App\Models\Group::find($data['group_id']);
                if ($group) {
                    $data['recipients_count'] = $group->enrollments()->where('status', 'active')->count();
                }
            }
        } else {
            $data['status'] = 'scheduled';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // إذا كانت الرسالة مرسلة فوراً، نرسل الإشعارات
        if ($record->status === 'sent') {
            if ($record->send_notification) {
                $this->sendNotifications($record);
            }
        }
    }

    protected function sendNotifications($message): void
    {
        $group = $message->group;
        if (!$group) return;

        // إرسال إشعار لجميع الطلاب المسجلين في المجموعة
        $enrollments = $group->enrollments()->where('status', 'active')->with('student.user')->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->student && $enrollment->student->user) {
                Notification::make()
                    ->title($message->subject)
                    ->body($message->message)
                    ->icon('heroicon-o-envelope')
                    ->iconColor('primary')
                    ->sendToDatabase($enrollment->student->user);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الرسالة بنجاح';
    }
}
