<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Illuminate\Support\Str;

/**
 * "Tap" Monolog untuk kanal log 'audit' -- menambahkan konteks yang sama
 * di setiap baris log (siapa, dari mana, request yang mana) supaya baris
 * log Audit::catat(...) di controller bisa fokus ke datanya sendiri saja,
 * tanpa perlu mengulang info ini di setiap pemanggilan.
 *
 * Laravel mengirim instance Illuminate\Log\Logger (BUKAN Monolog\Logger
 * langsung) ke class tap -- getHandlers() di sini jalan lewat __call()
 * milik Illuminate\Log\Logger yang meneruskannya ke Monolog\Logger asli
 * di baliknya.
 *
 * request_id disimpan di attributes Request (bukan properti statis kelas
 * ini) supaya tetap benar per-request walau proses PHP-nya dipakai ulang
 * lintas request (mis. di worker PHP-FPM/Octane).
 */
class TambahKonteksAudit
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function ($record) {
                $request = app()->bound('request') ? request() : null;
                $user = $request ? auth()->user() : null;

                $requestId = null;

                if ($request) {
                    if (! $request->attributes->has('audit_request_id')) {
                        $request->attributes->set('audit_request_id', (string) Str::uuid());
                    }

                    $requestId = $request->attributes->get('audit_request_id');
                }

                $konteks = [
                    'request_id' => $requestId,
                    'ip'         => $request?->ip(),
                    'user_agent' => $request?->userAgent(),
                    'user_id'    => $user?->id,
                    'user_nama'  => $user?->name,
                    'user_peran' => $user?->role,
                ];

                if (method_exists($record, 'with')) {
                    // Monolog 3.x: LogRecord adalah objek immutable (readonly).
                    return $record->with(extra: array_merge($record->extra, $konteks));
                }

                // Fallback untuk Monolog 2.x (array biasa), kalau-kalau versinya beda.
                $record['extra'] = array_merge($record['extra'] ?? [], $konteks);

                return $record;
            });
        }
    }
}
