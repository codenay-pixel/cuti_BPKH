<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

/**
 * 6 jenis cuti PNS sesuai PP 11/2017 tentang Manajemen PNS.
 * Seeder ini aman dijalankan berulang (idempotent): baris lama dicocokkan
 * lewat kode atau nama lamanya, sehingga leave_type_id yang sudah dipakai
 * pengajuan cuti tidak berubah.
 */
class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definisi() as $item) {
            $alias = $item['alias'];
            unset($item['alias']);

            $existing = LeaveType::where('kode', $item['kode'])->first()
                ?? LeaveType::whereNull('kode')->whereIn('nama_cuti', $alias)->first();

            if ($existing) {
                $existing->update($item);
            } else {
                LeaveType::create($item);
            }
        }

        // Bersihkan jenis cuti di luar 6 kode resmi (mis. jenis uji coba lama)
        // yang belum pernah dipakai pengajuan cuti.
        LeaveType::whereNull('kode')->whereDoesntHave('leaveRequests')->delete();
    }

    private function definisi(): array
    {
        return [
            [
                'kode' => LeaveType::TAHUNAN,
                'alias' => ['Cuti Tahunan'],
                'nama_cuti' => 'Cuti Tahunan',
                'urutan' => 1,
                'jatah_hari_default' => 12,
                'maks_hari' => null,
                'perlu_lampiran' => false,
                'mengurangi_saldo' => true,
                'dasar_hukum' => 'PP 11/2017 Pasal 310-314',
                'syarat_dokumen' => implode("\n", [
                    'Formulir Permintaan dan Pemberian Cuti (otomatis dibuat sistem).',
                    'Diajukan paling lambat 7 hari kerja sebelum tanggal mulai cuti (Nota Direktorat Jenderal Planologi Kehutanan).',
                    'Lampiran tambahan tidak wajib; unggah bila ada surat pendukung.',
                ]),
            ],
            [
                'kode' => LeaveType::SAKIT,
                'alias' => ['Cuti Sakit'],
                'nama_cuti' => 'Cuti Sakit',
                'urutan' => 2,
                'jatah_hari_default' => 0,
                'maks_hari' => 365,
                'perlu_lampiran' => false,
                'mengurangi_saldo' => false,
                'dasar_hukum' => 'PP 11/2017 Pasal 315-319',
                'syarat_dokumen' => implode("\n", [
                    'Lampirkan surat keterangan dokter bila ada (PDF/JPG, maks 2 MB).',
                    'Sakit 1-2 hari: cukup surat keterangan sakit dan pemberitahuan kepada atasan langsung.',
                    'Kecelakaan kerja: surat keterangan dokter dan surat keterangan dari atasan langsung.',
                ]),
            ],
            [
                'kode' => LeaveType::MELAHIRKAN,
                'alias' => ['Cuti Melahirkan'],
                'nama_cuti' => 'Cuti Melahirkan',
                'urutan' => 3,
                'jatah_hari_default' => 0,
                'maks_hari' => 90,
                'perlu_lampiran' => false,
                'mengurangi_saldo' => false,
                'dasar_hukum' => 'PP 11/2017 Pasal 325-329',
                'syarat_dokumen' => null,
            ],
            [
                'kode' => LeaveType::BESAR,
                'alias' => ['Cuti Besar'],
                'nama_cuti' => 'Cuti Besar',
                'urutan' => 4,
                'jatah_hari_default' => 0,
                'maks_hari' => 90,
                'perlu_lampiran' => false,
                'mengurangi_saldo' => false,
                'dasar_hukum' => 'PP 11/2017 Pasal 320-324',
                'syarat_dokumen' => 'Lampiran tambahan tidak wajib; unggah bila ada surat pendukung.',
            ],
            [
                'kode' => LeaveType::ALASAN_PENTING,
                'alias' => ['Cuti Alasan Penting', 'Cuti Karena Alasan Penting'],
                'nama_cuti' => 'Cuti Karena Alasan Penting',
                'urutan' => 5,
                'jatah_hari_default' => 0,
                'maks_hari' => 30,
                'perlu_lampiran' => false,
                'mengurangi_saldo' => false,
                'dasar_hukum' => 'PP 11/2017 Pasal 330-333',
                'syarat_dokumen' => 'Lampiran tambahan tidak wajib; unggah bila ada surat pendukung.',
            ],
            [
                'kode' => LeaveType::DILUAR_TANGGUNGAN,
                'alias' => ['Cuti Diluar Tanggungan Negara', 'Cuti di Luar Tanggungan Negara', 'Cuti Di Luar Tanggungan Negara'],
                'nama_cuti' => 'Cuti di Luar Tanggungan Negara',
                'urutan' => 6,
                'jatah_hari_default' => 0,
                'maks_hari' => 1095,
                'perlu_lampiran' => false,
                'mengurangi_saldo' => false,
                'dasar_hukum' => 'PP 11/2017 Pasal 334-339',
                'syarat_dokumen' => 'Lampiran tambahan tidak wajib; unggah bila ada surat pendukung.',
            ],
        ];
    }
}
