<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_approvals', function (Blueprint $table) {
            $table->boolean('sebagai_plh')->default(false)->after('keputusan');
        });
    }

    public function down(): void
    {
        Schema::table('leave_approvals', function (Blueprint $table) {
            $table->dropColumn('sebagai_plh');
        });
    }
};
