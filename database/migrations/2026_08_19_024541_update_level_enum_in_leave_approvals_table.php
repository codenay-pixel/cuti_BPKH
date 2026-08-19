<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE leave_approvals MODIFY COLUMN level ENUM('atasan', 'admin_hrd', 'atasan_langsung', 'kepala_balai') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE leave_approvals MODIFY COLUMN level ENUM('atasan', 'admin_hrd') NOT NULL");
    }
};