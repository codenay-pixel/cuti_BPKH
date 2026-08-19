<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
       
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('pegawai', 'kepala_divisi', 'atasan_langsung', 'atasan', 'admin') NOT NULL DEFAULT 'pegawai'");

        
        DB::table('users')->where('role', 'kepala_divisi')->update(['role' => 'atasan_langsung']);

      
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('pegawai', 'atasan_langsung', 'atasan', 'admin') NOT NULL DEFAULT 'pegawai'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('pegawai', 'kepala_divisi', 'atasan', 'admin') NOT NULL DEFAULT 'pegawai'");
        DB::table('users')->where('role', 'atasan_langsung')->update(['role' => 'kepala_divisi']);
    }
};