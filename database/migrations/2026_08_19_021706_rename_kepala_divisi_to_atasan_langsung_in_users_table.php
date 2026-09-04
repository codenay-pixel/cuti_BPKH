<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Migration ini aslinya mengganti nilai role 'kepala_divisi' -> 'atasan_langsung'
 * lewat "ALTER TABLE ... MODIFY COLUMN ... ENUM(...)" (sintaks khusus MySQL,
 * tidak jalan di PostgreSQL/Neon). Nilai 'atasan_langsung' sekarang sudah
 * dimasukkan langsung ke enum awal di migration pembuatan tabel users, dan
 * 'kepala_divisi' tidak pernah benar-benar dipakai di data manapun (bukan di
 * seeder, bukan di validasi) -- jadi untuk instalasi baru migration ini tidak
 * perlu melakukan apa-apa lagi. Dibiarkan sebagai file kosong (bukan dihapus)
 * supaya nomor urut migration lain yang datang setelahnya tidak berubah.
 */
return new class extends Migration
{
    public function up(): void
    {

    }

    public function down(): void
    {

    }
};
