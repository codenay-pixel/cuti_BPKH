<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveService
{
    /** Hak cuti tahunan setiap tahun (PP 11/2017 Pasal 310). */
    public const HAK_TAHUNAN = 12;

    /**
     * Sisa cuti tahunan tahun sebelumnya (N-1) dan dua tahun sebelumnya (N-2)
     * masing-masing hanya boleh dipakai paling banyak 6 hari kerja di tahun
     * berjalan (PP 11/2017 Pasal 313). Jadi maksimum yang bisa diambil dalam
     * satu tahun = 12 + 6 + 6 = 24 hari kerja.
     */
    public const MAKS_AKUMULASI = 6;

    /** Berapa tahun ke belakang yang masih bisa diakumulasikan. */
    public const TAHUN_AKUMULASI = 2;

    public function hitungHariKerja(string $mulai, string $selesai): int
    {
        $period = CarbonPeriod::create($mulai, $selesai);
        $hariKerja = 0;

        foreach ($period as $tanggal) {
            if (! $tanggal->isWeekend()) {
                $hariKerja++;
            }
        }

        return $hariKerja;
    }

    /**
     * Jenis "Cuti Tahunan". Mengembalikan null bila LeaveTypeSeeder belum
     * dijalankan, supaya halaman tidak error total.
     */
    public function jenisTahunan(): ?LeaveType
    {
        static $cache = false;

        if ($cache === false) {
            $cache = LeaveType::where('kode', LeaveType::TAHUNAN)->first();
        }

        return $cache;
    }

    /**
     * Pastikan baris saldo cuti tahunan untuk TAHUN BERJALAN ada.
     *
     * Saldo tahun lampau (N-1 dan N-2) sengaja TIDAK dibuat otomatis. Sisa cuti
     * tahun-tahun sebelumnya adalah data historis yang hanya diketahui bagian
     * kepegawaian — sesuai catatan pada formulir resmi: "diisi oleh pejabat yang
     * menangani bidang kepegawaian sebelum PNS mengajukan cuti". Membuatnya
     * otomatis dengan asumsi terpakai = 0 akan memberi pegawai 12 hari akumulasi
     * yang belum tentu benar-benar dia miliki.
     *
     * Admin mengisinya lewat menu Saldo Cuti.
     */
    public function pastikanSaldoTahunan(User $user, ?int $tahun = null): void
    {
        $tahun ??= now()->year;
        $jenis = $this->jenisTahunan();

        if (! $jenis) {
            return;
        }

        // Jangan buat saldo bila pegawai baru diangkat tahun depan.
        if ($user->tmt_pns !== null && $user->tmt_pns->year > $tahun) {
            return;
        }

        LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'leave_type_id' => $jenis->id, 'tahun' => $tahun],
            ['jatah' => self::HAK_TAHUNAN, 'terpakai' => 0],
        );
    }

    /**
     * Rincian saldo cuti tahunan yang bisa dipakai pada tahun berjalan.
     *
     * @return array{tahun:int, rincian:array<int,array<string,mixed>>, total_tersedia:int, total_sisa:int, terpakai_tahun_ini:int}
     */
    public function rincianSaldoTahunan(User $user, ?int $tahun = null): array
    {
        $tahun ??= now()->year;
        $jenis = $this->jenisTahunan();

        $saldo = $jenis
            ? LeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $jenis->id)
                ->whereBetween('tahun', [$tahun - self::TAHUN_AKUMULASI, $tahun])
                ->get()
                ->keyBy('tahun')
            : collect();

        $rincian = [];
        $totalTersedia = 0;
        $totalSisa = 0;

        for ($i = self::TAHUN_AKUMULASI; $i >= 0; $i--) {
            $t = $tahun - $i;
            $row = $saldo->get($t);

            $jatah = (int) ($row->jatah ?? 0);
            $terpakai = (int) ($row->terpakai ?? 0);
            $sisa = max(0, $jatah - $terpakai);

            // Tahun berjalan boleh dipakai penuh; tahun lampau dibatasi 6 hari.
            $tersedia = $i === 0 ? $sisa : min($sisa, self::MAKS_AKUMULASI);

            $rincian[] = [
                'tahun'     => $t,
                'label'     => $i === 0 ? 'Hak Tahun ' . $t : 'Sisa ' . $t,
                'catatan'   => $i === 0 ? 'Tahun berjalan' : 'Maks. ' . self::MAKS_AKUMULASI . ' hari',
                'jatah'     => $jatah,
                'terpakai'  => $terpakai,
                'sisa'      => $sisa,
                'tersedia'  => $tersedia,
                'ada'       => $row !== null,
            ];

            $totalTersedia += $tersedia;
            $totalSisa += $sisa;
        }

        return [
            'tahun'              => $tahun,
            'rincian'            => $rincian,
            'total_tersedia'     => $totalTersedia,
            'total_sisa'         => $totalSisa,
            'terpakai_tahun_ini' => (int) ($saldo->get($tahun)->terpakai ?? 0),
        ];
    }

    public function totalSaldoTahunanTersedia(User $user, ?int $tahun = null): int
    {
        return $this->rincianSaldoTahunan($user, $tahun)['total_tersedia'];
    }

    /**
     * Hari cuti tahunan yang masih "dipesan" oleh pengajuan yang belum final,
     * supaya pegawai tidak bisa mengajukan melebihi saldo dengan cara antre.
     */
    public function hariTertahan(User $user, ?int $tahun = null, ?int $kecualikanId = null): int
    {
        $tahun ??= now()->year;
        $jenis = $this->jenisTahunan();

        if (! $jenis) {
            return 0;
        }

        return (int) LeaveRequest::where('user_id', $user->id)
            ->where('leave_type_id', $jenis->id)
            ->whereIn('status', ['menunggu', 'disetujui_atasan'])
            ->whereYear('tanggal_mulai', $tahun)
            // Saat mengubah pengajuan, hari miliknya sendiri jangan ikut dihitung
            // sebagai "tertahan" — kalau tidak, saldonya terpakai dua kali.
            ->when($kecualikanId, fn ($q) => $q->where('id', '!=', $kecualikanId))
            ->sum('jumlah_hari');
    }

    public function ajukanCuti(array $data, User $user, ?UploadedFile $lampiran = null): LeaveRequest
    {
        $jumlahHari = $this->hitungHariKerja($data['tanggal_mulai'], $data['tanggal_selesai']);
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        if ($jumlahHari < 1) {
            throw new \Exception('Rentang tanggal yang dipilih tidak mengandung hari kerja.');
        }

        if ($leaveType->maks_hari && $jumlahHari > $leaveType->maks_hari) {
            throw new \Exception(
                $leaveType->nama_cuti . ' maksimal ' . $leaveType->maks_hari . ' hari. Anda mengajukan ' . $jumlahHari . ' hari.'
            );
        }

        if ($leaveType->mengurangi_saldo) {
            $this->pastikanSaldoTahunan($user);

            $tersedia = $this->totalSaldoTahunanTersedia($user);
            $tertahan = $this->hariTertahan($user);
            $sisaEfektif = $tersedia - $tertahan;

            if ($sisaEfektif < $jumlahHari) {
                throw new \Exception(
                    'Saldo cuti tahunan tidak mencukupi. Tersedia ' . max(0, $sisaEfektif) . ' hari'
                    . ($tertahan > 0 ? ' (sudah dikurangi ' . $tertahan . ' hari yang masih dalam proses)' : '')
                    . ', Anda mengajukan ' . $jumlahHari . ' hari.'
                );
            }
        }

        $penyetuju = $user->atasan_id;
        $status = 'menunggu';

        // Kepala Balai adalah puncak rantai persetujuan. Bila ia tidak memiliki
        // atasan di sistem, pengajuannya melewati tahap Atasan Langsung dan
        // langsung masuk ke antrean Persetujuan Final miliknya sendiri.
        if (! $penyetuju && $user->isKepalaBalai()) {
            $penyetuju = $user->id;
            $status = 'disetujui_atasan';
        }

        if (! $penyetuju) {
            throw new \Exception('Atasan langsung Anda belum diatur. Hubungi admin kepegawaian terlebih dahulu.');
        }

        $lampiranPath = $lampiran?->store('lampiran-cuti', 'public');

        return DB::transaction(function () use ($data, $user, $jumlahHari, $lampiranPath, $penyetuju, $status) {
            return LeaveRequest::create([
                'user_id'             => $user->id,
                'leave_type_id'       => $data['leave_type_id'],
                'tanggal_mulai'       => $data['tanggal_mulai'],
                'tanggal_selesai'     => $data['tanggal_selesai'],
                'jumlah_hari'         => $jumlahHari,
                'alasan'              => $data['alasan'],
                'alamat_cuti'         => $data['alamat_cuti'] ?? null,
                'telepon_cuti'        => $data['telepon_cuti'] ?? null,
                'lampiran'            => $lampiranPath,
                'status'              => $status,
                'current_approver_id' => $penyetuju,
            ]);
        });
    }

    /**
     * Ubah pengajuan yang belum diputuskan siapa pun.
     * Status dan tujuan persetujuan tidak diubah — hanya isinya.
     */
    public function perbaruiCuti(LeaveRequest $leaveRequest, array $data, ?UploadedFile $lampiran = null): LeaveRequest
    {
        $user = $leaveRequest->user;
        $jumlahHari = $this->hitungHariKerja($data['tanggal_mulai'], $data['tanggal_selesai']);
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        if ($jumlahHari < 1) {
            throw new \Exception('Rentang tanggal yang dipilih tidak mengandung hari kerja.');
        }

        if ($leaveType->maks_hari && $jumlahHari > $leaveType->maks_hari) {
            throw new \Exception(
                $leaveType->nama_cuti . ' maksimal ' . $leaveType->maks_hari . ' hari. Anda mengajukan ' . $jumlahHari . ' hari.'
            );
        }

        if ($leaveType->mengurangi_saldo) {
            $this->pastikanSaldoTahunan($user);

            $tersedia = $this->totalSaldoTahunanTersedia($user);
            $tertahan = $this->hariTertahan($user, null, $leaveRequest->id);
            $sisaEfektif = $tersedia - $tertahan;

            if ($sisaEfektif < $jumlahHari) {
                throw new \Exception(
                    'Saldo cuti tahunan tidak mencukupi. Tersedia ' . max(0, $sisaEfektif) . ' hari'
                    . ($tertahan > 0 ? ' (sudah dikurangi ' . $tertahan . ' hari yang masih dalam proses)' : '')
                    . ', perubahan Anda meminta ' . $jumlahHari . ' hari.'
                );
            }
        }

        return DB::transaction(function () use ($leaveRequest, $data, $jumlahHari, $lampiran) {
            $isi = [
                'leave_type_id'   => $data['leave_type_id'],
                'tanggal_mulai'   => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'jumlah_hari'     => $jumlahHari,
                'alasan'          => $data['alasan'],
                'alamat_cuti'     => $data['alamat_cuti'] ?? null,
                'telepon_cuti'    => $data['telepon_cuti'] ?? null,
            ];

            // Berkas lama baru dihapus setelah yang baru berhasil tersimpan.
            if ($lampiran) {
                $lama = $leaveRequest->lampiran;
                $isi['lampiran'] = $lampiran->store('lampiran-cuti', 'public');

                if ($lama) {
                    Storage::disk('public')->delete($lama);
                }
            }

            $leaveRequest->update($isi);

            return $leaveRequest->refresh();
        });
    }

    /**
     * Persetujuan final: potong saldo mulai dari tahun paling lama
     * (yang paling cepat hangus), dengan tetap menghormati batas 6 hari
     * untuk saldo tahun-tahun sebelumnya.
     */
    public function setujuiCutiFinal(LeaveRequest $leaveRequest): void
    {
        DB::transaction(function () use ($leaveRequest) {
            $leaveRequest->loadMissing('leaveType', 'user');

            if (! $leaveRequest->leaveType->mengurangi_saldo) {
                $leaveRequest->update([
                    'status' => 'disetujui',
                    'nomor_surat' => $leaveRequest->nomor_surat ?: $this->buatNomorSurat($leaveRequest),
                ]);

                return;
            }

            $tahun = now()->year;
            $jenis = $this->jenisTahunan();
            $sisaDipotong = (int) $leaveRequest->jumlah_hari;

            if (! $jenis) {
                throw new \Exception('Data jenis Cuti Tahunan belum tersedia. Jalankan seeder jenis cuti terlebih dahulu.');
            }

            $baris = LeaveBalance::where('user_id', $leaveRequest->user_id)
                ->where('leave_type_id', $jenis->id)
                ->whereBetween('tahun', [$tahun - self::TAHUN_AKUMULASI, $tahun])
                ->orderBy('tahun')
                ->lockForUpdate()
                ->get();

            $totalTersedia = 0;
            foreach ($baris as $row) {
                $sisa = max(0, $row->jatah - $row->terpakai);
                $totalTersedia += $row->tahun === $tahun ? $sisa : min($sisa, self::MAKS_AKUMULASI);
            }

            if ($totalTersedia < $sisaDipotong) {
                throw new \Exception('Saldo cuti tahunan tidak mencukupi saat finalisasi (tersedia ' . $totalTersedia . ' hari).');
            }

            foreach ($baris as $row) {
                if ($sisaDipotong <= 0) {
                    break;
                }

                $sisa = max(0, $row->jatah - $row->terpakai);
                $bolehDipakai = $row->tahun === $tahun ? $sisa : min($sisa, self::MAKS_AKUMULASI);
                $potong = min($bolehDipakai, $sisaDipotong);

                if ($potong > 0) {
                    $row->increment('terpakai', $potong);
                    $sisaDipotong -= $potong;
                }
            }

            $leaveRequest->update([
                'status' => 'disetujui',
                'nomor_surat' => $leaveRequest->nomor_surat ?: $this->buatNomorSurat($leaveRequest),
            ]);
        });
    }

    /**
     * Kembalikan saldo cuti tahunan yang sudah terpotong.
     *
     * Dipakai saat pengajuan yang sudah disetujui dibatalkan atau dihapus.
     * Pengembalian dilakukan dari tahun terlama, mengikuti urutan pemotongan
     * di setujuiCutiFinal(), dan tidak akan membuat "terpakai" jadi negatif.
     */
    public function kembalikanSaldo(LeaveRequest $leaveRequest): int
    {
        $leaveRequest->loadMissing('leaveType');

        if ($leaveRequest->status !== 'disetujui' || ! $leaveRequest->leaveType?->mengurangi_saldo) {
            return 0;
        }

        $jenis = $this->jenisTahunan();

        if (! $jenis) {
            return 0;
        }

        return DB::transaction(function () use ($leaveRequest, $jenis) {
            $sisaDikembalikan = (int) $leaveRequest->jumlah_hari;
            $totalKembali = 0;

            $baris = LeaveBalance::where('user_id', $leaveRequest->user_id)
                ->where('leave_type_id', $jenis->id)
                ->where('terpakai', '>', 0)
                ->orderBy('tahun')
                ->lockForUpdate()
                ->get();

            foreach ($baris as $row) {
                if ($sisaDikembalikan <= 0) {
                    break;
                }

                $kembali = min((int) $row->terpakai, $sisaDikembalikan);

                if ($kembali > 0) {
                    $row->decrement('terpakai', $kembali);
                    $sisaDikembalikan -= $kembali;
                    $totalKembali += $kembali;
                }
            }

            return $totalKembali;
        });
    }

    /** Nomor surat sederhana: 001/CUTI/BPKH/VIII/2026 */
    public function buatNomorSurat(LeaveRequest $leaveRequest): string
    {
        $urut = LeaveRequest::whereYear('created_at', $leaveRequest->created_at?->year ?? now()->year)
            ->where('id', '<=', $leaveRequest->id)
            ->count();

        $romawi = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return sprintf(
            '%03d/CUTI/BPKH/%s/%s',
            $urut,
            $romawi[now()->month],
            now()->year
        );
    }
}
