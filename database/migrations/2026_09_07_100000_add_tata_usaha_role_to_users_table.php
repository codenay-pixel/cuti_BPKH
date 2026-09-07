<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambahkan peran baru 'tata_usaha' ke kolom users.role.
 *
 * Catatan penting -- ini SENGAJA berbeda dari pola migration enum
 * sebelumnya (lihat 2026_08_19_021615 dan 2026_08_19_024541, yang
 * dibiarkan kosong dan nilai barunya cuma ditambahkan langsung ke array
 * enum pada migration pembuatan tabel). Pola lama itu berhasil untuk
 * instalasi baru (fresh migrate), TAPI tidak pernah benar-benar mengubah
 * constraint pada database yang sudah pernah di-migrate sebelumnya
 * (seperti database production di Neon) -- karena up()/down()-nya
 * kosong, tidak ada perintah SQL yang benar-benar jalan. Constraint lama
 * di database yang sudah ada tetap memakai daftar nilai yang lama sampai
 * seseorang mengubahnya manual lewat SQL di luar migration.
 *
 * Di sini dipakai ALTER TABLE ... DROP/ADD CONSTRAINT, sintaks yang valid
 * di PostgreSQL (beda dengan "MODIFY COLUMN ... ENUM(...)" yang dicoba
 * sebelumnya dan hanya berlaku di MySQL), jadi migration ini benar-benar
 * mengubah constraint-nya baik di instalasi baru maupun database lama
 * yang sudah berjalan.
 *
 * Nama constraint dicari lewat pg_constraint, bukan diasumsikan bernama
 * "users_role_check" -- supaya tetap benar meskipun nama constraint yang
 * dibuatkan Postgres/Doctrine berbeda dari perkiraan.
 */
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
