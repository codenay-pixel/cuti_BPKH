<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan berkas gambar tanda tangan pejabat (atasan langsung / kepala balai)
 * supaya bisa dicetak otomatis di Formulir Permintaan dan Pemberian Cuti.
 * Yang disimpan hanya path relatif di disk "public", bukan gambarnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tanda_tangan')) {
                $table->string('tanda_tangan')->nullable()->after('no_telp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tanda_tangan')) {
                $table->dropColumn('tanda_tangan');
            }
        });
    }
};
