<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('justice:backup')->dailyAt('02:00');
Schedule::command('queue:prune-failed --hours=168')->weekly();
Schedule::command('justice:ops-check')->weeklyOn(1, '03:00');
