<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    /** Kode tetap 6 jenis cuti PNS (PP 11/2017). */
    public const TAHUNAN            = 'tahunan';
    public const SAKIT              = 'sakit';
    public const MELAHIRKAN         = 'melahirkan';
    public const BESAR              = 'besar';
    public const ALASAN_PENTING     = 'alasan_penting';
    public const DILUAR_TANGGUNGAN  = 'diluar_tanggungan';

    protected $fillable = [
        'kode',
        'nama_cuti',
        'urutan',
        'jatah_hari_default',
        'maks_hari',
        'perlu_lampiran',
        'syarat_dokumen',
        'dasar_hukum',
        'mengurangi_saldo',
    ];

    protected function casts(): array
    {
        return [
            'perlu_lampiran' => 'boolean',
            'mengurangi_saldo' => 'boolean',
        ];
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /** Daftar 6 jenis cuti, selalu urut sesuai formulir resmi. */
    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    public function isTahunan(): bool
    {
        return $this->kode === self::TAHUNAN;
    }

    /** Syarat dokumen dipecah jadi array baris untuk ditampilkan sebagai daftar. */
    public function syaratList(): array
    {
        if (blank($this->syarat_dokumen)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $this->syarat_dokumen)
        )));
    }
}
