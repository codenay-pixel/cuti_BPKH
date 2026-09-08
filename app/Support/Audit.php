<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class Audit
{
    public static function catat(string $aksi, array $data = []): void
    {
        Log::channel('audit')->info($aksi, $data);
    }
}
