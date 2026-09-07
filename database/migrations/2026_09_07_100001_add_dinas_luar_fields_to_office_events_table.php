<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan dua kolom untuk fitur pencatatan Dinas Luar oleh Tata Usaha:
 * - nomor_spt: nomor Surat Perintah Tugas, diisi untuk kegiatan jenis dinas_luar.
 * - dicatat_oleh_id: siapa yang menginput acara ini (bisa beda dari user_id,
 *   yaitu pegawai yang bersangkutan, saat Tata Usaha/Admin mencatatkan
 *   acara atas nama pegawai lain).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_events', function (Blueprint $table) {
            $table->string('nomor_spt', 50)->nullable()->after('jenis_lainnya');
            $table->foreignId('dicatat_oleh_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('office_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dicatat_oleh_id');
            $table->dropColumn('nomor_spt');
        });
    }
};
