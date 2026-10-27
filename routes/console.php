<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule exam reminders to run daily at 8 AM
Schedule::command('exams:send-reminders')->dailyAt('08:00');

// Schedule installments status update to run daily at 12:00 AM (midnight)
Schedule::command('installments:update-status')
    ->daily()
    ->at('00:00')
    ->description('تحديث حالة الأقساط وتحديد المتأخرة منها');
