<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('leave_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('leave_type_id')->constrained();
        $table->date('tanggal_mulai');
        $table->date('tanggal_selesai');
        $table->integer('jumlah_hari');
        $table->text('alasan');
        $table->string('lampiran')->nullable();
        $table->enum('status', ['menunggu', 'disetujui_atasan', 'disetujui', 'ditolak'])
              ->default('menunggu');
        $table->foreignId('current_approver_id')->nullable()->constrained('users');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('leave_requests');
}
};
