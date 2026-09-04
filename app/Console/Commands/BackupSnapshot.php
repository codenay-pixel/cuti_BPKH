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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupSnapshot extends Command
{
    protected $signature = 'backup:snapshot';

    protected $description = 'Simpan snapshot seluruh data cuti ke tabel system_backups dan ke file terpisah (R2/S3 di production)';

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

        $json = json_encode($payload, JSON_PRETTY_PRINT);

        DB::table('system_backups')->insert([
            'payload' => $json,
            'created_at' => now(),
        ]);

        DB::table('system_backups')->where('created_at', '<', now()->subDays(90))->delete();

        try {
            $nama = 'backups/cuti-backup-' . now()->format('Y-m-d_His') . '.json';

            Storage::disk('public')->put($nama, $json);

            $batas = now()->subDays(90);

            foreach (Storage::disk('public')->files('backups') as $file) {
                $waktu = Storage::disk('public')->lastModified($file);

                if ($waktu && \Carbon\Carbon::createFromTimestamp($waktu)->lt($batas)) {
                    Storage::disk('public')->delete($file);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan salinan backup ke disk terpisah: ' . $e->getMessage());
        }

        $this->info('Snapshot data berhasil disimpan.');

        return self::SUCCESS;
    }
}
