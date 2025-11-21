<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:reminder-dokumen-commands')->everyMinute();
Schedule::command('app:reminder-crew-dokumen-commands')->everyMinute();
Schedule::command('app:reminder-crew-sertifikat-commands')->everyMinute();
Schedule::command('app:reminder-crew-kontrak-commands')->everyMinute();
