<?php

namespace App\Filament\Instructor\Resources\SessionSchedules\Pages;

use App\Filament\Instructor\Resources\SessionSchedules\SessionScheduleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSessionSchedule extends CreateRecord
{
    protected static string $resource = SessionScheduleResource::class;

    public function getTitle(): string
    {
        return __('filament/instructor/create_session_schedule.title');
    }

    protected function afterCreate(): void
    {
        // Check if there are conflicts
        if ($this->record->has_conflicts) {
            $conflictsData = $this->record->conflicts_data;
            
            $message = 'تم إنشاء السيشن ولكن هناك تضاربات:';
            
            if (isset($conflictsData['instructor'])) {
                $message .= "\n- لديك سيشنات أخرى في نفس الوقت";
            }
            if (isset($conflictsData['classroom'])) {
                $message .= "\n- القاعة محجوزة في نفس الوقت";
            }
            if (isset($conflictsData['students'])) {
                $message .= "\n- بعض الطلاب لديهم سيشنات أخرى";
            }

            Notification::make()
                ->title(__('filament/instructor/create_session_schedule.تحذير:تضارباتفيالجدول'))
                ->body($message)
                ->warning()
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->title(__('filament/instructor/create_session_schedule.تمجدولةالسيشنبنجاح'))
                ->body('لا توجد تضاربات في الجدول')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/create_session_schedule.title');
    }

}
