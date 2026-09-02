<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mencatat apakah keputusan level kepala_balai ini dibuat oleh Plh
 * (Pelaksana Harian), bukan Kepala Balai sendiri. Dicatat pada saat
 * keputusan dibuat (bukan dibaca ulang dari status Plh saat ini) supaya
 * formulir yang dicetak nanti tetap akurat secara historis walau status
 * Plh sudah berubah/dinonaktifkan setelahnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_approvals', function (Blueprint $table) {
            $table->boolean('sebagai_plh')->default(false)->after('keputusan');
        });
    }

    public function down(): void
    {
        Schema::table('leave_approvals', function (Blueprint $table) {
            $table->dropColumn('sebagai_plh');
        });
    }
};
