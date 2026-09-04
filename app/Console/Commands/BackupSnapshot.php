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

/**
 * Snapshot seluruh data penting (bukan tabel framework seperti sessions/cache)
 * ke DUA tempat:
 *
 *  1. Tabel system_backups -- cepat diakses dari dalam aplikasi, tapi TIDAK
 *     aman sebagai satu-satunya backup karena ada di database yang sama
 *     dengan data aslinya (kalau database Neon-nya hilang/corrupt, baris
 *     ini ikut hilang).
 *  2. File JSON di disk 'public' (folder backups/) -- inilah yang membuat
 *     backup ini benar-benar "di luar" database. Selama PUBLIC_DISK_DRIVER
 *     production diset ke s3 (lihat config/filesystems.php), file ini
 *     otomatis tersimpan di Cloudflare R2, terpisah total dari Neon.
 *     Kalau PUBLIC_DISK_DRIVER masih 'local' (mis. di komputer development),
 *     file ini cuma tersimpan lokal seperti biasa -- tidak ada bedanya.
 *
 * Dipanggil dari docker-entrypoint.sh setiap container start -- bukan cron
 * sungguhan, karena aplikasi ini belum punya proses scheduler yang jalan
 * terus di server (lihat catatan di routes/console.php). Dibatasi maksimal
 * sekali per ~20 jam supaya restart berkali-kali dalam sehari tidak bikin
 * snapshot berulang percuma.
 */
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

        // Retensi 90 hari supaya tabel ini tidak membengkak selamanya.
        DB::table('system_backups')->where('created_at', '<', now()->subDays(90))->delete();

        // Salinan kedua, di luar database -- lihat penjelasan di atas kelas.
        // Sengaja tidak menggagalkan perintah ini kalau upload gagal (mis.
        // kredensial R2 belum/salah diisi): backup ke tabel di atas tetap
        // tersimpan, jadi aplikasi tidak terganggu.
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
