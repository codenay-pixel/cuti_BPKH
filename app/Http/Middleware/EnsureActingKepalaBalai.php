<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengizinkan akses ke halaman Persetujuan Final (Kepala Balai) bagi Kepala
 * Balai sendiri, ATAU pegawai Atasan Langsung yang sedang ditunjuk admin
 * sebagai Plh (Pelaksana Harian) lewat kolom users.is_plh_kepala_balai.
 * Menggantikan middleware 'role:atasan' khusus untuk rute ini.
 */
class EnsureActingKepalaBalai
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->bisaBertindakSebagaiKepalaBalai()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
