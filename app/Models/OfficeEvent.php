<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeEvent extends Model
{
    protected $fillable = [
        'user_id',
        'nama_acara',
        'jenis',
        'jenis_lainnya',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'keterangan',
        'lampiran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public const JENIS = [
        'dinas_luar' => 'Dinas Luar Kota',
        'rapat'      => 'Rapat / Undangan',
        'diklat'     => 'Diklat / Pelatihan',
        'lainnya'    => 'Lainnya',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getJenisLabelAttribute(): string
    {
        if ($this->jenis === 'lainnya' && filled($this->jenis_lainnya)) {
            return $this->jenis_lainnya;
        }

        return self::JENIS[$this->jenis] ?? ucfirst((string) $this->jenis);
    }
}
