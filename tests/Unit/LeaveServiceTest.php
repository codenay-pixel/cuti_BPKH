<?php

namespace Tests\Unit;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji logika inti perhitungan cuti: hari kerja, saldo tahunan (termasuk
 * akumulasi lintas tahun maks. 6 hari), pengajuan, persetujuan final
 * (pemotongan saldo), dan pengembalian saldo saat dibatalkan.
 *
 * Ini bagian paling rawan salah di aplikasi -- kalau ada perubahan kode di
 * LeaveService nanti, jalankan test ini dulu (php artisan test) sebelum
 * di-deploy, supaya perhitungan saldo pegawai tidak keliru tanpa disadari.
 */
class LeaveServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeaveService $service;

    private LeaveType $tahunan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LeaveService();

        $this->tahunan = LeaveType::create([
            'kode' => LeaveType::TAHUNAN,
            'nama_cuti' => 'Cuti Tahunan',
            'urutan' => 1,
            'jatah_hari_default' => 12,
            'maks_hari' => null,
            'perlu_lampiran' => false,
            'mengurangi_saldo' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function buatPegawai(array $override = []): User
    {
        return User::factory()->create(array_merge(['role' => 'pegawai'], $override));
    }

    public function test_hitung_hari_kerja_mengecualikan_akhir_pekan(): void
    {
        $senin = Carbon::now()->next(Carbon::MONDAY);
        $jumat = $senin->copy()->addDays(4);
        $seninDepan = $senin->copy()->addWeek();
        $sabtu = $senin->copy()->addDays(5);
        $minggu = $senin->copy()->addDays(6);

        $this->assertSame(5, $this->service->hitungHariKerja($senin->toDateString(), $jumat->toDateString()));
        $this->assertSame(6, $this->service->hitungHariKerja($senin->toDateString(), $seninDepan->toDateString()));
        $this->assertSame(0, $this->service->hitungHariKerja($sabtu->toDateString(), $minggu->toDateString()));
    }

    public function test_pastikan_saldo_tahunan_membuat_baris_sekali_saja(): void
    {
        $user = $this->buatPegawai();

        $this->service->pastikanSaldoTahunan($user, 2026);
        $this->service->pastikanSaldoTahunan($user, 2026);

        $this->assertSame(1, LeaveBalance::where('user_id', $user->id)->where('tahun', 2026)->count());

        $baris = LeaveBalance::where('user_id', $user->id)->where('tahun', 2026)->first();
        $this->assertSame(12, $baris->jatah);
        $this->assertSame(0, $baris->terpakai);
    }

    public function test_pastikan_saldo_tahunan_dilewati_untuk_pegawai_yang_belum_diangkat(): void
    {
        $user = $this->buatPegawai(['tmt_pns' => '2027-01-01']);

        $this->service->pastikanSaldoTahunan($user, 2026);

        $this->assertSame(0, LeaveBalance::where('user_id', $user->id)->count());
    }

    public function test_rincian_saldo_tahunan_membatasi_akumulasi_tahun_lampau_maksimal_6_hari(): void
    {
        $user = $this->buatPegawai();

        LeaveBalance::create(['user_id' => $user->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2024, 'jatah' => 12, 'terpakai' => 0]); // sisa 12, tersedia dibatasi 6
        LeaveBalance::create(['user_id' => $user->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2025, 'jatah' => 12, 'terpakai' => 10]); // sisa 2, di bawah batas
        LeaveBalance::create(['user_id' => $user->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2026, 'jatah' => 12, 'terpakai' => 0]); // tahun berjalan, penuh

        $rincian = $this->service->rincianSaldoTahunan($user, 2026);

        $this->assertSame(6 + 2 + 12, $rincian['total_tersedia']);
        $this->assertSame(12 + 2 + 12, $rincian['total_sisa']);
    }

    public function test_hari_tertahan_hanya_menjumlah_pengajuan_yang_masih_dalam_proses(): void
    {
        $user = $this->buatPegawai();

        $buat = fn (string $status, int $hari) => LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => '2026-03-02',
            'tanggal_selesai' => '2026-03-03',
            'jumlah_hari' => $hari,
            'alasan' => 'Uji hitung hari tertahan untuk saldo',
            'status' => $status,
        ]);

        $buat('menunggu', 3);
        $buat('disetujui_atasan', 2);
        $buat('ditolak', 5);
        $buat('disetujui', 4);

        $this->assertSame(5, $this->service->hariTertahan($user, 2026));
    }

    public function test_ajukan_cuti_menetapkan_atasan_langsung_sebagai_penyetuju_pertama(): void
    {
        $atasan = User::factory()->create(['role' => 'atasan_langsung']);
        $pegawai = $this->buatPegawai(['atasan_id' => $atasan->id]);

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $leaveRequest = $this->service->ajukanCuti([
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->copy()->addDays(2)->toDateString(),
            'alasan' => 'Uji penentuan penyetuju pertama pengajuan cuti',
        ], $pegawai);

        $this->assertSame('menunggu', $leaveRequest->status);
        $this->assertSame($atasan->id, $leaveRequest->current_approver_id);
        $this->assertSame(3, $leaveRequest->jumlah_hari);
    }

    public function test_ajukan_cuti_kepala_balai_tanpa_atasan_masuk_ke_antreannya_sendiri(): void
    {
        $kepalaBalai = User::factory()->create(['role' => 'atasan']);

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $leaveRequest = $this->service->ajukanCuti([
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->toDateString(),
            'alasan' => 'Uji kepala balai sebagai penyetuju dirinya sendiri',
        ], $kepalaBalai);

        $this->assertSame('disetujui_atasan', $leaveRequest->status);
        $this->assertSame($kepalaBalai->id, $leaveRequest->current_approver_id);
    }

    public function test_ajukan_cuti_gagal_jika_pegawai_belum_punya_atasan(): void
    {
        $pegawai = $this->buatPegawai();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Atasan langsung Anda belum diatur');

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $this->service->ajukanCuti([
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->toDateString(),
            'alasan' => 'Uji pengajuan gagal tanpa atasan terdaftar',
        ], $pegawai);
    }

    public function test_ajukan_cuti_gagal_jika_saldo_tidak_cukup(): void
    {
        $atasan = User::factory()->create(['role' => 'atasan_langsung']);
        $pegawai = $this->buatPegawai(['atasan_id' => $atasan->id]);

        $this->service->pastikanSaldoTahunan($pegawai, (int) now()->year);
        LeaveBalance::where('user_id', $pegawai->id)->update(['terpakai' => 12]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Saldo cuti tahunan tidak mencukupi');

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $this->service->ajukanCuti([
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->copy()->addDays(1)->toDateString(),
            'alasan' => 'Uji pengajuan gagal karena saldo sudah habis',
        ], $pegawai);
    }

    public function test_setujui_cuti_final_memotong_saldo_dari_tahun_paling_lama(): void
    {
        Carbon::setTestNow('2026-06-15');

        $pegawai = $this->buatPegawai();

        LeaveBalance::create(['user_id' => $pegawai->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2024, 'jatah' => 12, 'terpakai' => 8]); // sisa 4
        LeaveBalance::create(['user_id' => $pegawai->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2025, 'jatah' => 12, 'terpakai' => 0]); // sisa 12, dibatasi 6
        LeaveBalance::create(['user_id' => $pegawai->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2026, 'jatah' => 12, 'terpakai' => 0]);

        $leaveRequest = LeaveRequest::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => '2026-06-15',
            'tanggal_selesai' => '2026-06-19',
            'jumlah_hari' => 7, // ambil 4 sisa dari 2024, sisanya 3 dari 2025
            'alasan' => 'Uji pemotongan saldo lintas tahun saat final',
            'status' => 'disetujui_atasan',
        ]);

        $this->service->setujuiCutiFinal($leaveRequest);

        $this->assertSame('disetujui', $leaveRequest->fresh()->status);
        $this->assertNotNull($leaveRequest->fresh()->nomor_surat);
        $this->assertSame(12, LeaveBalance::where('tahun', 2024)->value('terpakai')); // 8+4, habis
        $this->assertSame(3, LeaveBalance::where('tahun', 2025)->value('terpakai'));  // 0+3
        $this->assertSame(0, LeaveBalance::where('tahun', 2026)->value('terpakai'));  // tidak tersentuh
    }

    public function test_setujui_cuti_final_gagal_jika_saldo_tidak_cukup_saat_finalisasi(): void
    {
        Carbon::setTestNow('2026-06-15');

        $pegawai = $this->buatPegawai();

        LeaveBalance::create(['user_id' => $pegawai->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2026, 'jatah' => 12, 'terpakai' => 10]); // sisa 2

        $leaveRequest = LeaveRequest::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => '2026-06-15',
            'tanggal_selesai' => '2026-06-16',
            'jumlah_hari' => 5,
            'alasan' => 'Uji finalisasi gagal karena saldo tidak cukup',
            'status' => 'disetujui_atasan',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('tidak mencukupi saat finalisasi');

        $this->service->setujuiCutiFinal($leaveRequest);
    }

    public function test_kembalikan_saldo_mengembalikan_dari_tahun_paling_lama(): void
    {
        $pegawai = $this->buatPegawai();

        LeaveBalance::create(['user_id' => $pegawai->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2024, 'jatah' => 12, 'terpakai' => 4]);
        LeaveBalance::create(['user_id' => $pegawai->id, 'leave_type_id' => $this->tahunan->id, 'tahun' => 2025, 'jatah' => 12, 'terpakai' => 3]);

        $leaveRequest = LeaveRequest::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-01-06',
            'jumlah_hari' => 6,
            'alasan' => 'Uji pengembalian saldo saat pengajuan dibatalkan',
            'status' => 'disetujui',
        ]);

        $kembali = $this->service->kembalikanSaldo($leaveRequest);

        $this->assertSame(6, $kembali);
        $this->assertSame(0, LeaveBalance::where('tahun', 2024)->value('terpakai')); // 4-4
        $this->assertSame(1, LeaveBalance::where('tahun', 2025)->value('terpakai')); // 3-2
    }

    public function test_kembalikan_saldo_nol_jika_status_bukan_disetujui(): void
    {
        $pegawai = $this->buatPegawai();

        $leaveRequest = LeaveRequest::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $this->tahunan->id,
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-01-02',
            'jumlah_hari' => 2,
            'alasan' => 'Uji tidak ada pengembalian untuk status menunggu',
            'status' => 'menunggu',
        ]);

        $this->assertSame(0, $this->service->kembalikanSaldo($leaveRequest));
    }
}
