<?php

namespace Tests\Feature;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji alur persetujuan cuti dari ujung ke ujung lewat HTTP -- pengajuan
 * pegawai, persetujuan Atasan Langsung, lalu persetujuan final Kepala
 * Balai -- sekaligus memastikan saldo cuti terpotong dengan benar dan
 * bahwa atasan yang bukan wewenangnya tidak bisa menyetujui/menolak
 * pengajuan orang lain.
 */
class LeaveApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    private function buatJenisTahunan(): LeaveType
    {
        return LeaveType::create([
            'kode' => LeaveType::TAHUNAN,
            'nama_cuti' => 'Cuti Tahunan',
            'urutan' => 1,
            'jatah_hari_default' => 12,
            'perlu_lampiran' => false,
            'mengurangi_saldo' => true,
        ]);
    }

    public function test_alur_persetujuan_lengkap_sampai_saldo_terpotong(): void
    {
        $jenis = $this->buatJenisTahunan();

        $kepalaBalai = User::factory()->create(['role' => 'atasan']);
        $atasan = User::factory()->create(['role' => 'atasan_langsung', 'atasan_id' => $kepalaBalai->id]);
        $pegawai = User::factory()->create(['role' => 'pegawai', 'atasan_id' => $atasan->id]);

        LeaveBalance::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $jenis->id,
            'tahun' => now()->year,
            'jatah' => 12,
            'terpakai' => 0,
        ]);

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $leaveRequest = app(LeaveService::class)->ajukanCuti([
            'leave_type_id' => $jenis->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->copy()->addDays(2)->toDateString(),
            'alasan' => 'Uji alur persetujuan penuh sampai final disetujui',
        ], $pegawai);

        $this->assertSame('menunggu', $leaveRequest->status);
        $this->assertSame($atasan->id, $leaveRequest->current_approver_id);

        // Atasan langsung menyetujui -> diteruskan ke Kepala Balai
        $this->actingAs($atasan)
            ->post(route('approval.approve', $leaveRequest), ['catatan' => 'Disetujui, silakan lanjut.'])
            ->assertSessionHasNoErrors();

        $leaveRequest->refresh();
        $this->assertSame('disetujui_atasan', $leaveRequest->status);
        $this->assertSame($kepalaBalai->id, $leaveRequest->current_approver_id);

        // Kepala Balai menyetujui final -> saldo terpotong
        $this->actingAs($kepalaBalai)
            ->post(route('kepala-balai.approval.approve', $leaveRequest), ['catatan' => 'Disetujui final.'])
            ->assertSessionHasNoErrors();

        $leaveRequest->refresh();
        $this->assertSame('disetujui', $leaveRequest->status);
        $this->assertNull($leaveRequest->current_approver_id);
        $this->assertNotNull($leaveRequest->nomor_surat);

        $saldo = LeaveBalance::where('user_id', $pegawai->id)->where('tahun', now()->year)->first();
        $this->assertSame(3, $saldo->terpakai);
    }

    public function test_atasan_langsung_menolak_tidak_memotong_saldo(): void
    {
        $jenis = $this->buatJenisTahunan();

        $atasan = User::factory()->create(['role' => 'atasan_langsung']);
        $pegawai = User::factory()->create(['role' => 'pegawai', 'atasan_id' => $atasan->id]);

        LeaveBalance::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $jenis->id,
            'tahun' => now()->year,
            'jatah' => 12,
            'terpakai' => 0,
        ]);

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $leaveRequest = app(LeaveService::class)->ajukanCuti([
            'leave_type_id' => $jenis->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->copy()->addDays(1)->toDateString(),
            'alasan' => 'Uji penolakan oleh atasan langsung tidak memotong saldo',
        ], $pegawai);

        $this->actingAs($atasan)
            ->post(route('approval.reject', $leaveRequest), ['catatan' => 'Belum bisa, ada tugas mendesak di kantor.'])
            ->assertSessionHasNoErrors();

        $leaveRequest->refresh();
        $this->assertSame('ditolak', $leaveRequest->status);
        $this->assertNull($leaveRequest->current_approver_id);

        $saldo = LeaveBalance::where('user_id', $pegawai->id)->where('tahun', now()->year)->first();
        $this->assertSame(0, $saldo->terpakai);
    }

    public function test_atasan_lain_tidak_bisa_menyetujui_pengajuan_yang_bukan_wewenangnya(): void
    {
        $jenis = $this->buatJenisTahunan();

        $atasanAsli = User::factory()->create(['role' => 'atasan_langsung']);
        $atasanLain = User::factory()->create(['role' => 'atasan_langsung']);
        $pegawai = User::factory()->create(['role' => 'pegawai', 'atasan_id' => $atasanAsli->id]);

        LeaveBalance::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $jenis->id,
            'tahun' => now()->year,
            'jatah' => 12,
            'terpakai' => 0,
        ]);

        $senin = Carbon::now()->next(Carbon::MONDAY);

        $leaveRequest = app(LeaveService::class)->ajukanCuti([
            'leave_type_id' => $jenis->id,
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->toDateString(),
            'alasan' => 'Uji otorisasi -- bukan atasan yang berwenang menyetujui',
        ], $pegawai);

        $this->actingAs($atasanLain)
            ->post(route('approval.approve', $leaveRequest), ['catatan' => 'Mencoba menyetujui pengajuan orang lain'])
            ->assertForbidden();

        $this->assertSame('menunggu', $leaveRequest->fresh()->status);
    }
}
