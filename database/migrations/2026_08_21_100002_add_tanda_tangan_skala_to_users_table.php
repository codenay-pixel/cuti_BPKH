<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tanda_tangan_skala')) {
                $table->unsignedSmallInteger('tanda_tangan_skala')
                    ->default(120)
                    ->after('tanda_tangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tanda_tangan_skala')) {
                $table->dropColumn('tanda_tangan_skala');
            }
        });
    }
};
