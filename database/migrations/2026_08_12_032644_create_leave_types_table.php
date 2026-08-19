<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('leave_types', function (Blueprint $table) {
        $table->id();
        $table->string('nama_cuti');
        $table->integer('jatah_hari_default')->default(12);
        $table->boolean('perlu_lampiran')->default(false);
        $table->boolean('mengurangi_saldo')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('leave_types');
}
};
