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
        $table->enum('role', ['pegawai', 'atasan', 'admin'])->default('pegawai')->after('email');
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
