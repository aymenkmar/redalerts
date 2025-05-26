<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Website Monitoring Scheduled Tasks
Schedule::command('websites:monitor-status')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('websites:monitor-domain-ssl')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
