<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Schedule::command('app:update-contract-pkl-status')->everyMinute();
Schedule::command('app:update-document-status')->everyMinute();
Schedule::command('app:reminder-crew-documents')->everyMinute();
Schedule::command('app:reminder-crew-certificates')->everyMinute();

//php artisan schedule:work 2026-07-25