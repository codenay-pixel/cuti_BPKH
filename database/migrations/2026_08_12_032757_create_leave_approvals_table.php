<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('leave_approvals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
        $table->foreignId('approver_id')->constrained('users');
        // Daftar nilai final level (termasuk 'atasan_langsung' dan
        // 'kepala_balai' yang aslinya ditambahkan lewat migration terpisah).
        $table->enum('level', ['atasan', 'admin_hrd', 'atasan_langsung', 'kepala_balai']);
        $table->enum('keputusan', ['disetujui', 'ditolak'])->nullable();
        $table->text('catatan')->nullable();
        $table->timestamp('tanggal_keputusan')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('leave_approvals');
}
};
