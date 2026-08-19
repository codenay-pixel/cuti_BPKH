<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin HRD',
            'email' => 'admin@bpkh.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nip' => '198001012000011001',
            'jabatan' => 'Kepala HRD',
        ]);

        $atasan = User::create([
            'name' => 'Budi Santoso',
            'email' => 'atasan@bpkh.go.id',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
            'nip' => '198202022005011002',
            'jabatan' => 'Kepala Bidang',
        ]);

        $pegawai = User::create([
            'name' => 'Siti Aminah',
            'email' => 'pegawai@bpkh.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
            'nip' => '199003032010012003',
            'jabatan' => 'Staff',
            'atasan_id' => $atasan->id,
        ]);

        // Buat saldo cuti tahun berjalan untuk pegawai
        $cutiTahunan = LeaveType::where('nama_cuti', 'Cuti Tahunan')->first();

        LeaveBalance::create([
            'user_id' => $pegawai->id,
            'leave_type_id' => $cutiTahunan->id,
            'tahun' => now()->year,
            'jatah' => 12,
            'terpakai' => 0,
        ]);
    }
}
