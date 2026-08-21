<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun contoh. Login memakai NIP + password (default: password123).
 * Seeder ini idempotent, aman dijalankan ulang.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->buat([
            'nip'        => '198001012000011001',
            'name'       => 'Admin Kepegawaian',
            'email'      => 'admin@bpkh.go.id',
            'role'       => 'admin',
            'jabatan'    => 'Kepala Subbagian Tata Usaha',
            'unit_kerja' => 'Balai Pemantapan Kawasan Hutan',
            'tmt_pns'    => '2000-01-01',
        ]);

        $kepalaBalai = $this->buat([
            'nip'        => '197505052001121001',
            'name'       => 'Herban Heryandana',
            'email'      => 'kepala@bpkh.go.id',
            'role'       => 'atasan',
            'jabatan'    => 'Kepala Balai',
            'unit_kerja' => 'Balai Pemantapan Kawasan Hutan',
            'tmt_pns'    => '2001-12-01',
        ]);

        $atasanLangsung = $this->buat([
            'nip'        => '198202022005011002',
            'name'       => 'Budi Santoso',
            'email'      => 'atasan@bpkh.go.id',
            'role'       => 'atasan_langsung',
            'jabatan'    => 'Kepala Seksi Pemolaan Kawasan Hutan',
            'unit_kerja' => 'Seksi Pemolaan Kawasan Hutan',
            'tmt_pns'    => '2005-01-01',
            'atasan_id'  => $kepalaBalai->id,
        ]);

        $pegawai = $this->buat([
            'nip'        => '199003032010012003',
            'name'       => 'Siti Aminah',
            'email'      => 'pegawai@bpkh.go.id',
            'role'       => 'pegawai',
            'jabatan'    => 'Analis Kehutanan',
            'unit_kerja' => 'Seksi Pemolaan Kawasan Hutan',
            'tmt_pns'    => '2010-01-01',
            'no_telp'    => '081234567890',
            'atasan_id'  => $atasanLangsung->id,
        ]);

        // Atasan langsung juga bisa mengajukan cuti ke Kepala Balai
        $this->saldoTahunan($pegawai);
        $this->saldoTahunan($atasanLangsung);
        $this->saldoTahunan($admin);
    }

    private function buat(array $data): User
    {
        $nip = $data['nip'];
        unset($data['nip']);

        $data['password'] = Hash::make('password123');

        return User::updateOrCreate(['nip' => $nip], $data);
    }

    /** Buat saldo cuti tahunan untuk 3 tahun terakhir (N-2, N-1, N). */
    private function saldoTahunan(User $user): void
    {
        $jenis = LeaveType::where('kode', LeaveType::TAHUNAN)->first();

        if (! $jenis) {
            return;
        }

        for ($i = 2; $i >= 0; $i--) {
            LeaveBalance::updateOrCreate(
                [
                    'user_id'       => $user->id,
                    'leave_type_id' => $jenis->id,
                    'tahun'         => now()->year - $i,
                ],
                ['jatah' => 12],
            );
        }
    }
}
