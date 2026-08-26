<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tugas Terjadwal
|--------------------------------------------------------------------------
| Membuka hak cuti tahunan seluruh pegawai setiap 1 Januari pukul 00.05.
|
| Ini hanya PENGAMAN. Baris saldo tahun berjalan sebenarnya sudah dibuat
| sendiri begitu pegawai membuka Beranda / Cuti Saya, atau saat admin
| membuka halaman Saldo Cuti. Penjadwalan ini memastikan seluruh pegawai
| punya barisnya sejak hari pertama, termasuk yang jarang membuka aplikasi.
|
| Agar benar-benar berjalan, satu perintah penjadwal harus hidup di server:
|
|   Linux/VPS  ->  tambahkan ke crontab:
|                  * * * * * cd /var/www/cuti && php artisan schedule:run >> /dev/null 2>&1
|
|   Windows    ->  Task Scheduler, jalankan tiap menit:
|                  php artisan schedule:run
|
| Tanpa itu, jadwal ini tidak akan menyala — dan aplikasinya tetap baik-baik
| saja, karena saldo dibuat otomatis saat halaman dibuka.
*/
Schedule::command('backup:snapshot')->daily();

Schedule::command('cuti:buka-tahun')
    ->yearlyOn(1, 1, '00:05')
    ->onSuccess(fn () => logger()->info('Hak cuti tahunan ' . now()->year . ' dibuka otomatis.'))
    ->onFailure(fn () => logger()->error('Gagal membuka hak cuti tahunan ' . now()->year . '.'));
