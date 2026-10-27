<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SmsLog;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmsStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalSms = SmsLog::count();
        $sentSms = SmsLog::sent()->count();
        $failedSms = SmsLog::failed()->count();
        $todaySms = SmsLog::today()->count();

        $successRate = $totalSms > 0 ? round(($sentSms / $totalSms) * 100, 1) : 0;

        // Get last 7 days data for chart
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7Days[] = SmsLog::whereDate('sent_at', $date)->count();
        }

        return [
            Stat::make('إجمالي الرسائل', number_format($totalSms))
                ->description('جميع الرسائل النصية')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->chart($last7Days),

            Stat::make('رسائل مرسلة', number_format($sentSms))
                ->description("{$successRate}% معدل النجاح")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('رسائل فاشلة', number_format($failedSms))
                ->description($failedSms > 0 ? 'تحقق من الإعدادات' : 'لا توجد مشاكل')
                ->descriptionIcon($failedSms > 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check')
                ->color($failedSms > 0 ? 'danger' : 'success'),

            Stat::make('رسائل اليوم', number_format($todaySms))
                ->description('تم إرسالها اليوم')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),
        ];
    }
}
