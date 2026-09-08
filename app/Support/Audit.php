<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Helper tipis buat nyatet jejak audit ("siapa ngapain, kapan") lewat kanal
 * log khusus 'audit' (lihat config/logging.php) -- terpisah dari log error
 * bawaan Laravel, dan formatnya JSON terstruktur (lihat TambahKonteksAudit)
 * biar gampang ditelusuri/di-filter kalau suatu saat perlu investigasi.
 *
 * Pemakaian: Audit::catat('cuti.diajukan', ['leave_request_id' => $lr->id]);
 */
class Audit
{
    public static function catat(string $aksi, array $data = []): void
    {
        Log::channel('audit')->info($aksi, $data);
    }
}
