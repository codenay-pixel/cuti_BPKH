<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('nip')->unique()->nullable()->after('name');
        // Daftar nilai final role (termasuk 'atasan_langsung' yang aslinya
        // ditambahkan lewat migration terpisah). Digabung di sini supaya
        // instalasi baru tidak perlu lagi lewat langkah "kepala_divisi" yang
        // hanya pernah dipakai sebentar sebagai nama transisi.
        $table->enum('role', ['pegawai', 'atasan_langsung', 'atasan', 'admin'])->default('pegawai')->after('email');
        $table->foreignId('atasan_id')->nullable()->constrained('users')->nullOnDelete()->after('role');
        $table->string('jabatan')->nullable();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['atasan_id']);
        $table->dropColumn(['nip', 'role', 'atasan_id', 'jabatan']);
    });
}
};
