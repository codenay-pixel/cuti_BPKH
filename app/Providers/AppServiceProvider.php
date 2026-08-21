<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Nama hari dan bulan tampil dalam Bahasa Indonesia
        // (translatedFormat('d F Y') -> "19 Agustus 2026").
        Carbon::setLocale('id');

        Blade::if('peran', function (string ...$roles) {
            return auth()->check() && in_array(auth()->user()->role, $roles, true);
        });
    }
}
