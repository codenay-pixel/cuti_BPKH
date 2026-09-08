<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akun pegawai yang "dihapus" sekarang dinonaktifkan (soft delete), bukan
 * dibuang permanen dari database. Sebelum ini, menghapus pegawai lewat menu
 * Kelola Pegawai langsung membuang barisnya -- dan karena office_events.user_id
 * dipasang cascadeOnDelete(), seluruh riwayat Dinas Luar/kegiatan pegawai itu
 * ikut lenyap juga. Dengan soft delete, riwayat cuti dan kegiatan tetap
 * tersimpan dan tetap muncul di laporan/rekap, sementara akunnya sendiri
 * otomatis tidak bisa dipakai login lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
