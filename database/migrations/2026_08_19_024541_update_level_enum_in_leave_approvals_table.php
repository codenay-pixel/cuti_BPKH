<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Sama seperti migration rename kepala_divisi: nilai 'atasan_langsung' dan
 * 'kepala_balai' sudah dimasukkan langsung ke enum awal di migration
 * pembuatan tabel leave_approvals, sehingga instalasi baru tidak perlu lagi
 * "ALTER TABLE ... MODIFY COLUMN ... ENUM(...)" (sintaks khusus MySQL, tidak
 * jalan di PostgreSQL/Neon). Dibiarkan sebagai file kosong supaya nomor urut
 * migration lain tidak berubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
