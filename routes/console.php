<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:snapshot')->daily();

Schedule::command('cuti:buka-tahun')
    ->yearlyOn(1, 1, '00:05')
    ->onSuccess(fn () => logger()->info('Hak cuti tahunan ' . now()->year . ' dibuka otomatis.'))
    ->onFailure(fn () => logger()->error('Gagal membuka hak cuti tahunan ' . now()->year . '.'));
