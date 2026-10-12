<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pulse snapshot server + ingest + trim (17.72). pulse:check tanpa --once
// adalah daemon loop; di scheduler dipakai --once tiap menit agar aman.
Schedule::command('pulse:check', ['--once'])
    ->everyMinute()
    ->withoutOverlapping();
