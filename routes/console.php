<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:sync-tenders')
    ->hourly()
    ->then(fn () => Artisan::call('app:sync-lots'));

Schedule::command('tenders:sync-suppliers')->daily();
Schedule::command('app:send-weekly-report')->weeklyOn(1, '08:00');

Schedule::call(function () {
    foreach (glob(storage_path('logs/sync-*.log')) as $file) {
        if (filemtime($file) < strtotime('-1 month')) {
            unlink($file);
        }
    }
})->monthly();
