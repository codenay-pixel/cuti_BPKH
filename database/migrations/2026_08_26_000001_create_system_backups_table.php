<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tempat menyimpan snapshot berkala seluruh data cuti, sebagai jaring
 * pengaman tambahan di luar window restore 6 jam bawaan Neon (lihat
 * App\Console\Commands\BackupSnapshot). Baris lama otomatis dibuang
 * setelah 90 hari supaya tabel ini tidak terus membengkak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};
