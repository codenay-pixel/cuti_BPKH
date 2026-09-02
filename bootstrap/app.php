<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (dan platform serupa) menerima trafik HTTPS di edge-nya,
        // lalu meneruskan ke container ini lewat HTTP biasa. Tanpa baris
        // ini, Laravel tidak tahu request aslinya HTTPS — akibatnya asset(),
        // url(), dan redirect bisa salah menghasilkan link http:// yang
        // diblokir browser (mixed content) di halaman https://.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'kepala_balai' => \App\Http\Middleware\EnsureActingKepalaBalai::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();