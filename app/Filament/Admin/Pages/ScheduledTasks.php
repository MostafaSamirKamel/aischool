<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ScheduledTasks extends Page
{
    use HasPageShield;
    protected string $view = 'filament.admin.pages.scheduled-tasks';

    protected static ?string $navigationLabel = 'المهام المجدولة';

    protected static ?string $title = 'إدارة المهام المجدولة';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إعدادات النظام';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->hasRole('super_admin'));
    }

    public function runInstallmentsUpdate()
    {
        try {
            Artisan::call('installments:update-status');
            $output = Artisan::output();
            
            Notification::make()
                ->success()
                ->title('تم تحديث حالة الأقساط')
                ->body($output)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('حدث خطأ')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function runExamReminders()
    {
        try {
            Artisan::call('exams:send-reminders');
            $output = Artisan::output();
            
            Notification::make()
                ->success()
                ->title('تم إرسال تذكيرات الامتحانات')
                ->body($output)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('حدث خطأ')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function runAllScheduled()
    {
        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();
            
            Notification::make()
                ->success()
                ->title('تم تشغيل جميع المهام المجدولة')
                ->body($output)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('حدث خطأ')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getScheduledTasksList()
    {
        try {
            Artisan::call('schedule:list');
            return Artisan::output();
        } catch (\Exception $e) {
            return 'خطأ في عرض المهام: ' . $e->getMessage();
        }
    }

    public function getCronJobCommand()
    {
        return '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1';
    }

    public function getScheduledTasks()
    {
        return [
            [
                'name' => 'تحديث حالة الأقساط',
                'command' => 'installments:update-status',
                'schedule' => 'يومياً الساعة 00:00',
                'description' => 'تحديث حالة الأقساط وتحديد المتأخرة منها',
                'action' => 'runInstallmentsUpdate',
            ],
            [
                'name' => 'إرسال تذكيرات الامتحانات',
                'command' => 'exams:send-reminders',
                'schedule' => 'يومياً الساعة 08:00',
                'description' => 'إرسال تذكيرات للطلاب بالامتحانات القادمة',
                'action' => 'runExamReminders',
            ],
        ];
    }
}
