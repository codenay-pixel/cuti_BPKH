<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OfficeEvent extends Model
{
    protected $fillable = [
        'user_id',
        'dicatat_oleh_id',
        'nama_acara',
        'jenis',
        'jenis_lainnya',
        'nomor_spt',
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

    /**
     * URL lampiran/surat dinas acara untuk ditampilkan/dibuka di halaman web.
     * Memakai Storage::disk('public')->url() supaya otomatis mengikuti
     * disk yang sedang aktif (lokal atau R2/S3 -- lihat PUBLIC_DISK_DRIVER
     * di config/filesystems.php).
     */
    public function getLampiranUrlAttribute(): ?string
    {
        return $this->lampiran
            ? Storage::disk('public')->url(str_replace('\\', '/', $this->lampiran))
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Pegawai yang menginput acara ini (bisa berbeda dari user() bila dicatatkan oleh Tata Usaha/Admin). */
    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_id');
    }

    public function getJenisLabelAttribute(): string
    {
        if ($this->jenis === 'lainnya' && filled($this->jenis_lainnya)) {
            return $this->jenis_lainnya;
        }

        return self::JENIS[$this->jenis] ?? ucfirst((string) $this->jenis);
    }

    /** Lama kegiatan dalam hari, inklusif tanggal mulai dan selesai. */
    public function getLamaHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function scopeDinasLuar($query)
    {
        return $query->where('jenis', 'dinas_luar');
    }
}
