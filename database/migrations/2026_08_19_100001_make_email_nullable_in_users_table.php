<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('unit_kerja')->nullable()->after('jabatan');
            $table->date('tmt_pns')->nullable()->after('unit_kerja');
            $table->string('no_telp')->nullable()->after('tmt_pns');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['unit_kerja', 'tmt_pns', 'no_telp']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
