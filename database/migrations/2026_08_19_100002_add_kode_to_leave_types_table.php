<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('kode', 30)->nullable()->unique()->after('id');
            $table->integer('urutan')->default(0)->after('nama_cuti');
            $table->integer('maks_hari')->nullable()->after('jatah_hari_default');
            $table->text('syarat_dokumen')->nullable()->after('perlu_lampiran');
            $table->text('dasar_hukum')->nullable()->after('syarat_dokumen');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['kode', 'urutan', 'maks_hari', 'syarat_dokumen', 'dasar_hukum']);
        });
    }
};
