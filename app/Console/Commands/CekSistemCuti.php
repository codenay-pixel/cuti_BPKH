<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OfficeEvent;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CekSistemCuti extends Command
{
    protected $signature = 'cuti:cek';

    protected $description = 'Memeriksa kesiapan database & data aplikasi cuti pegawai';

    private int $masalah = 0;

    public function handle(LeaveService $leaveService): int
    {
        $this->newLine();
        $this->line('<fg=white;bg=blue> CEK SISTEM ' . strtoupper(config('app.name', 'SICUTI')) . ' </>');
        $this->newLine();

        $this->cekKolom();
        $this->cekJenisCuti();
        $this->cekPengguna();
        $this->cekSaldo($leaveService);
        $this->cekData();

        $this->newLine();

        if ($this->masalah === 0) {
            $this->info('✔ Semua pemeriksaan lolos. Sistem siap dipakai.');
        } else {
            $this->warn('✖ Ada ' . $this->masalah . ' hal yang perlu dibereskan (lihat tanda ✖ dan ! di atas).');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    /* ---------------------------------------------------------------- */

    private function cekKolom(): void
    {
        $this->line('<options=bold>1. Struktur tabel (hasil migrate)</>');

        $wajib = [
            'users' => ['nip', 'role', 'atasan_id', 'jabatan', 'unit_kerja', 'tmt_pns', 'no_telp'],
            'leave_types' => ['kode', 'urutan', 'maks_hari', 'syarat_dokumen', 'dasar_hukum'],
            'leave_requests' => ['alamat_cuti', 'telepon_cuti', 'nomor_surat'],
            'office_events' => ['nama_acara', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'lampiran'],
        ];

        foreach ($wajib as $tabel => $kolom) {
            if (! Schema::hasTable($tabel)) {
                $this->baris(false, "Tabel {$tabel} belum ada — jalankan: php artisan migrate");
                continue;
            }

            $hilang = array_values(array_filter($kolom, fn ($k) => ! Schema::hasColumn($tabel, $k)));

            $hilang
                ? $this->baris(false, "Tabel {$tabel}: kolom hilang → " . implode(', ', $hilang))
                : $this->baris(true, "Tabel {$tabel} lengkap (" . count($kolom) . ' kolom baru)');
        }

        $this->newLine();
    }

    private function cekJenisCuti(): void
    {
        $this->line('<options=bold>2. Jenis cuti (hasil LeaveTypeSeeder)</>');

        $harus = [
            LeaveType::TAHUNAN, LeaveType::SAKIT, LeaveType::MELAHIRKAN,
            LeaveType::BESAR, LeaveType::ALASAN_PENTING, LeaveType::DILUAR_TANGGUNGAN,
        ];

        $ada = LeaveType::whereIn('kode', $harus)->pluck('kode')->all();
        $kurang = array_values(array_diff($harus, $ada));

        if ($kurang) {
            $this->baris(false, 'Jenis cuti belum lengkap, kurang: ' . implode(', ', $kurang)
                . ' — jalankan: php artisan db:seed --class=LeaveTypeSeeder');
        } else {
            $this->baris(true, '6 jenis cuti resmi tersedia');
        }

        $liar = LeaveType::whereNull('kode')->get();
        if ($liar->isNotEmpty()) {
            $this->baris(null, 'Masih ada jenis cuti lama tanpa kode (dipakai pengajuan, sengaja tidak dihapus): '
                . $liar->pluck('nama_cuti')->implode(', '));
        }

        $tanpaSyarat = LeaveType::whereNotNull('kode')->where('perlu_lampiran', true)
            ->whereNull('syarat_dokumen')->pluck('nama_cuti');
        if ($tanpaSyarat->isNotEmpty()) {
            $this->baris(false, 'Wajib lampiran tapi syarat dokumen kosong: ' . $tanpaSyarat->implode(', '));
        }

        $this->newLine();
    }

    private function cekPengguna(): void
    {
        $this->line('<options=bold>3. Pengguna & alur persetujuan</>');

        $total = User::count();
        $this->baris(true, "{$total} akun terdaftar");

        foreach (['atasan_langsung' => 'Atasan Langsung', 'atasan' => 'Kepala Balai', 'admin' => 'Admin Kepegawaian'] as $role => $label) {
            $n = User::where('role', $role)->count();
            $n > 0
                ? $this->baris(true, "{$label}: {$n} akun")
                : $this->baris(false, "Belum ada akun ber-peran {$label} — pengajuan cuti akan tersangkut");
        }

        $tanpaNip = User::whereNull('nip')->orWhere('nip', '')->get();
        $tanpaNip->isNotEmpty()
            ? $this->baris(false, 'Akun tanpa NIP (tidak bisa login): ' . $tanpaNip->pluck('name')->implode(', '))
            : $this->baris(true, 'Semua akun punya NIP');

        $nipAneh = User::whereNotNull('nip')->get()
            ->filter(fn ($u) => ! ctype_digit((string) $u->nip)
                || strlen((string) $u->nip) < 8 || strlen((string) $u->nip) > 18);
        if ($nipAneh->isNotEmpty()) {
            $this->baris(false, 'NIP tidak valid (harus angka 8-18 digit): '
                . $nipAneh->map(fn ($u) => $u->name . ' (' . $u->nip . ')')->implode(', '));
        }

        $nipPendek = User::whereNotNull('nip')->get()
            ->filter(fn ($u) => ctype_digit((string) $u->nip) && strlen((string) $u->nip) !== 18);
        if ($nipPendek->isNotEmpty()) {
            $this->baris(null, 'NIP bukan 18 digit — tetap bisa login, tapi di formulir cetak tidak diformat '
                . 'seperti NIP PNS: ' . $nipPendek->map(fn ($u) => $u->name . ' (' . $u->nip . ')')->implode(', '));
        }

        // Kepala Balai adalah puncak rantai, wajar tidak punya atasan.
        $tanpaAtasan = User::whereNull('atasan_id')->get()
            ->filter(fn (User $u) => $u->perluAtasanLangsung());
        $blokir = $tanpaAtasan->whereIn('role', ['pegawai', 'atasan_langsung']);
        $ringan = $tanpaAtasan->where('role', 'admin');

        if ($blokir->isNotEmpty()) {
            $this->baris(false, 'Belum punya atasan langsung, TIDAK BISA mengajukan cuti: '
                . $blokir->pluck('name')->implode(', '));
        }

        if ($ringan->isNotEmpty()) {
            $this->baris(null, 'Admin belum punya atasan langsung — isi hanya bila admin juga perlu mengajukan cuti: '
                . $ringan->pluck('name')->implode(', '));
        }

        if ($tanpaAtasan->isEmpty()) {
            $this->baris(true, 'Semua akun sudah punya atasan langsung');
        }

        $tanpaTmt = User::whereNull('tmt_pns')->pluck('name');
        if ($tanpaTmt->isNotEmpty()) {
            $this->baris(null, 'TMT PNS kosong (masa kerja di formulir jadi "-", saldo tahun lampau tidak dibuat otomatis): '
                . $tanpaTmt->implode(', '));
        }

        $this->newLine();
    }

    private function cekSaldo(LeaveService $leaveService): void
    {
        $this->line('<options=bold>4. Saldo cuti tahunan ' . now()->year . '</>');

        $jenis = $leaveService->jenisTahunan();

        if (! $jenis) {
            $this->baris(false, 'Jenis Cuti Tahunan belum ada, saldo tidak bisa dihitung');
            $this->newLine();

            return;
        }

        $tanpaSaldo = User::whereDoesntHave('leaveBalances', fn ($q) => $q
            ->where('leave_type_id', $jenis->id)->where('tahun', now()->year))->pluck('name');

        $tanpaSaldo->isNotEmpty()
            ? $this->baris(null, 'Belum punya saldo tahun ini (dibuat otomatis saat membuka Beranda): ' . $tanpaSaldo->implode(', '))
            : $this->baris(true, 'Semua akun punya saldo tahun berjalan');

        $rows = User::orderBy('name')->get()->map(function ($u) use ($leaveService) {
            $s = $leaveService->rincianSaldoTahunan($u);
            $r = collect($s['rincian'])->keyBy('tahun');

            return [
                $u->name,
                $u->role_label,
                ($r[now()->year - 2]['sisa'] ?? 0) . ' → ' . ($r[now()->year - 2]['tersedia'] ?? 0),
                ($r[now()->year - 1]['sisa'] ?? 0) . ' → ' . ($r[now()->year - 1]['tersedia'] ?? 0),
                $r[now()->year]['tersedia'] ?? 0,
                $s['total_tersedia'] . ' hari',
            ];
        });

        $this->table(
            ['Nama', 'Peran', 'Sisa ' . (now()->year - 2), 'Sisa ' . (now()->year - 1), 'Hak ' . now()->year, 'DAPAT DIAMBIL'],
            $rows
        );

        $this->line('  <fg=gray>"sisa → tersedia" = sisa saldo tahun itu, lalu berapa yang boleh dipakai tahun ini (maks 6).</>');
        $this->newLine();
    }

    private function cekData(): void
    {
        $this->line('<options=bold>5. Data pengajuan</>');

        $total = LeaveRequest::count();
        $this->baris(true, "{$total} pengajuan cuti, " . OfficeEvent::count() . ' acara kalender');

        if ($total > 0) {
            foreach (LeaveRequest::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status') as $s => $n) {
                $this->line("  <fg=gray>· {$s}: {$n}</>");
            }

            $tanpaAlamat = LeaveRequest::whereNull('alamat_cuti')->count();
            if ($tanpaAlamat > 0) {
                $this->baris(null, "{$tanpaAlamat} pengajuan lama belum punya alamat/telepon selama cuti — "
                    . 'di formulir cetak akan tampil "-". Pengajuan baru wajib mengisinya.');
            }
        }

        $this->newLine();
    }

    /* ---------------------------------------------------------------- */

    /** @param bool|null $ok true = lolos, false = masalah, null = peringatan */
    private function baris(?bool $ok, string $pesan): void
    {
        if ($ok === true) {
            $this->line("  <fg=green>✔</> {$pesan}");
        } elseif ($ok === false) {
            $this->line("  <fg=red>✖</> {$pesan}");
            $this->masalah++;
        } else {
            $this->line("  <fg=yellow>!</> {$pesan}");
        }
    }
}
