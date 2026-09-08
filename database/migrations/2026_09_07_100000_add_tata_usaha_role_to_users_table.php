<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function namaConstraintRole(): ?string
    {
        $row = DB::selectOne(<<<'SQL'
            SELECT con.conname
            FROM pg_constraint con
            JOIN pg_class rel ON rel.oid = con.conrelid
            JOIN pg_attribute att ON att.attrelid = rel.oid AND att.attnum = ANY (con.conkey)
            WHERE con.contype = 'c'
              AND rel.relname = 'users'
              AND att.attname = 'role'
            LIMIT 1
        SQL);

        return $row?->conname;
    }

    public function up(): void
    {
        if ($nama = $this->namaConstraintRole()) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT \"{$nama}\"");
        }

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('pegawai', 'atasan_langsung', 'atasan', 'admin', 'tata_usaha'))");
    }

    public function down(): void
    {
        if ($nama = $this->namaConstraintRole()) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT \"{$nama}\"");
        }

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('pegawai', 'atasan_langsung', 'atasan', 'admin'))");
    }
};
