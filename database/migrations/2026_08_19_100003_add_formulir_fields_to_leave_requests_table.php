<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('alamat_cuti')->nullable()->after('alasan');
            $table->string('telepon_cuti', 30)->nullable()->after('alamat_cuti');
            $table->string('nomor_surat')->nullable()->after('telepon_cuti');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['alamat_cuti', 'telepon_cuti', 'nomor_surat']);
        });
    }
};
