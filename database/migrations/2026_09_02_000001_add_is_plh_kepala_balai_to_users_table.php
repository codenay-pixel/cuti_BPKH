<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menandai satu pegawai Atasan Langsung sebagai Plh (Pelaksana Harian)
 * Kepala Balai saat ini. Diatur admin lewat halaman Kelola Pegawai.
 * Hanya boleh ada satu baris bernilai true dalam satu waktu -- dijaga di
 * App\Http\Controllers\Admin\UserController, bukan lewat constraint DB,
 * supaya tetap portable antara MySQL dan PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_plh_kepala_balai')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_plh_kepala_balai');
        });
    }
};
