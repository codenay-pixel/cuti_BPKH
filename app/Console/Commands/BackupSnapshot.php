<?php

namespace App\Console\Commands;

use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OfficeEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Snapshot seluruh data penting (bukan tabel framework seperti sessions/cache)
 * ke tabel system_backups. Dipanggil dari docker-entrypoint.sh setiap
 * container start -- bukan cron sungguhan, karena aplikasi ini belum punya
 * proses scheduler yang jalan terus di server (lihat catatan di
 * routes/console.php). Dibatasi maksimal sekali per ~20 jam supaya restart
 * berkali-kali dalam sehari tidak bikin snapshot berulang percuma.
 */
class BackupSnapshot extends Command
{
    protected $signature = 'backup:snapshot';

    protected $description = 'Simpan snapshot seluruh data cuti ke tabel system_backups';

    public function handle(): int
    {
        $last = DB::table('system_backups')->orderByDesc('created_at')->first();

        if ($last && now()->diffInHours($last->created_at) < 20) {
            $this->info('Snapshot terakhir masih kurang dari 20 jam lalu, dilewati.');

            return self::SUCCESS;
        }

        $payload = [
            'dibuat_pada' => now()->toIso8601String(),
            'users' => User::all()->toArray(),
            'leave_types' => LeaveType::all()->toArray(),
            'leave_balances' => LeaveBalance::all()->toArray(),
            'leave_requests' => LeaveRequest::all()->toArray(),
            'leave_approvals' => LeaveApproval::all()->toArray(),
            'office_events' => OfficeEvent::all()->toArray(),
        ];

        DB::table('system_backups')->insert([
            'payload' => json_encode($payload),
            'created_at' => now(),
        ]);

        // Retensi 90 hari supaya tabel ini tidak membengkak selamanya.
        DB::table('system_backups')->where('created_at', '<', now()->subDays(90))->delete();

        $this->info('Snapshot data berhasil disimpan.');

        return self::SUCCESS;
    }
}
