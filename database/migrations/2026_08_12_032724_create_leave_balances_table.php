<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('leave_balances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
        $table->year('tahun');
        $table->integer('jatah');
        $table->integer('terpakai')->default(0);
        $table->timestamps();

        $table->unique(['user_id', 'leave_type_id', 'tahun']);
    });
}

public function down(): void
{
    Schema::dropIfExists('leave_balances');
}
};
