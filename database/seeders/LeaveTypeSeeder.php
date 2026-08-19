<?php
namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['nama_cuti' => 'Cuti Tahunan', 'jatah_hari_default' => 12, 'perlu_lampiran' => false, 'mengurangi_saldo' => true],
            ['nama_cuti' => 'Cuti Sakit', 'jatah_hari_default' => 0, 'perlu_lampiran' => true, 'mengurangi_saldo' => false],
            ['nama_cuti' => 'Cuti Melahirkan', 'jatah_hari_default' => 90, 'perlu_lampiran' => true, 'mengurangi_saldo' => false],
            ['nama_cuti' => 'Cuti Besar', 'jatah_hari_default' => 0, 'perlu_lampiran' => false, 'mengurangi_saldo' => false],
            ['nama_cuti' => 'Cuti Alasan Penting', 'jatah_hari_default' => 0, 'perlu_lampiran' => false, 'mengurangi_saldo' => false],
        ];

        foreach ($types as $type) {
            LeaveType::create($type);
        }
    }
}