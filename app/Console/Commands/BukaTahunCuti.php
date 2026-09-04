<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Console\Command;

/**
 * Membuka hak cuti tahunan untuk seluruh pegawai pada tahun tertentu.
 *
 * Dijalankan sekali setiap awal tahun:
 *     php artisan cuti:buka-tahun
 *
 * Aman diulang — baris yang sudah ada tidak ditimpa, sehingga jumlah hari
 * yang terlanjur terpakai tidak akan hilang.
 */
class BukaTahunCuti extends Command
{
    protected $signature = 'cuti:buka-tahun
                            {--tahun= : Tahun yang dibuka (bawaan: tahun berjalan)}
                            {--jatah=12 : Hak cuti tahunan dalam hari}
                            {--coba : Hanya menampilkan rencana, tidak menyimpan}';

    protected $description = 'Membuat saldo cuti tahunan untuk seluruh pegawai pada tahun berjalan';

    public function handle(LeaveService $leaveService): int
    {
        $tahun = (int) ($this->option('tahun') ?: now()->year);
        $jatah = (int) $this->option('jatah');
        $coba  = (bool) $this->option('coba');

        $jenis = $leaveService->jenisTahunan();

        if (! $jenis) {
            $this->error('Jenis "Cuti Tahunan" belum ada. Jalankan: php artisan db:seed --class=LeaveTypeSeeder');

            return self::FAILURE;
        }

        if ($tahun < now()->year - LeaveService::TAHUN_AKUMULASI || $tahun > now()->year + 1) {
            $this->error("Tahun {$tahun} di luar jangkauan yang wajar ("
                . (now()->year - LeaveService::TAHUN_AKUMULASI) . '–' . (now()->year + 1) . ').');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line("<fg=white;bg=blue> MEMBUKA HAK CUTI TAHUNAN {$tahun} </>");
        $this->newLine();

        $dibuat = 0;
        $dilewati = 0;
        $belumMenjabat = 0;

        foreach (User::orderBy('name')->cursor() as $user) {

            if ($user->tmt_pns !== null && $user->tmt_pns->year > $tahun) {
                $belumMenjabat++;
                continue;
            }

            $ada = LeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $jenis->id)
                ->where('tahun', $tahun)
                ->exists();

            if ($ada) {
                $dilewati++;
                continue;
            }

            if (! $coba) {
                LeaveBalance::create([
                    'user_id'       => $user->id,
                    'leave_type_id' => $jenis->id,
                    'tahun'         => $tahun,
                    'jatah'         => $jatah,
                    'terpakai'      => 0,
                ]);
            }

            $this->line("  <fg=green>+</> {$user->name} — {$jatah} hari");
            $dibuat++;
        }

        $this->newLine();
        $this->line("  Dibuat        : <options=bold>{$dibuat}</> pegawai");
        $this->line("  Sudah ada     : {$dilewati} pegawai (tidak diubah)");

        if ($belumMenjabat > 0) {
            $this->line("  Belum menjabat: {$belumMenjabat} pegawai (TMT PNS setelah {$tahun})");
        }

        if ($coba) {
            $this->newLine();
            $this->warn('  Mode coba — tidak ada yang disimpan. Hapus --coba untuk menjalankan sungguhan.');
        }

        $this->newLine();
        $this->info('Selesai. Sisa cuti ' . ($tahun - 1) . ' dan ' . ($tahun - 2)
            . ' tetap terbawa, masing-masing maksimal ' . LeaveService::MAKS_AKUMULASI . ' hari.');
        $this->newLine();

        return self::SUCCESS;
    }
}
