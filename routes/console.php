<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule appointment reminders (24 hours before)
Schedule::command('appointments:send-reminders --hours=24')->hourly();

// Schedule appointment reminders (1 hour before)
Schedule::command('appointments:send-reminders --hours=1')->hourly();

// Schedule MOT reminders (30 days, 14 days, 7 days, 3 days before expiry)
Schedule::command('mot:send-reminders --days=30')->dailyAt('09:00');
Schedule::command('mot:send-reminders --days=14')->dailyAt('09:15');
Schedule::command('mot:send-reminders --days=7')->dailyAt('09:30');
Schedule::command('mot:send-reminders --days=3')->dailyAt('09:45');

// Schedule service due reminders (annual)
Schedule::command('service:send-reminders --days=30')->dailyAt('10:00');

// Schedule review requests (1 day after job completion)
Schedule::command('reviews:send')->daily()->at('11:00');

// Schedule backup
Schedule::command('backup:run')->daily()->at('02:00');
