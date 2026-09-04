<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {

        Carbon::setLocale('id');

        Blade::if('peran', function (string ...$roles) {
            return auth()->check() && in_array(auth()->user()->role, $roles, true);
        });
    }
}
