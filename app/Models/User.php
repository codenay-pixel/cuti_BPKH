<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'role',
        'atasan_id',
        'jabatan',
        'unit_kerja',
        'tmt_pns',
        'no_telp',
        'tanda_tangan',
        'tanda_tangan_skala',
        'is_plh_kepala_balai',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tmt_pns' => 'date',
            'password' => 'hashed',
            'tanda_tangan_skala' => 'integer',
            'is_plh_kepala_balai' => 'boolean',
        ];
    }

    public const TTD_SKALA_MIN     = 60;
    public const TTD_SKALA_MAX     = 180;
    public const TTD_SKALA_DEFAULT = 120;
    public const TTD_TINGGI_DASAR  = 30;

    public const ROLE_LABEL = [
        'pegawai'         => 'Pegawai',
        'atasan_langsung' => 'Atasan Langsung',
        'atasan'          => 'Kepala Balai',
        'admin'           => 'Admin Kepegawaian',
        'tata_usaha'      => 'Tata Usaha',
    ];

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id')->withTrashed();
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function officeEvents()
    {
        return $this->hasMany(OfficeEvent::class);
    }

    public function officeEventsDicatat()
    {
        return $this->hasMany(OfficeEvent::class, 'dicatat_oleh_id');
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function isAtasanLangsung(): bool
    {
        return $this->role === 'atasan_langsung';
    }

    public function isKepalaBalai(): bool
    {
        return $this->role === 'atasan';
    }

    public function isAtasan(): bool
    {
        return in_array($this->role, ['atasan_langsung', 'atasan'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTataUsaha(): bool
    {
        return $this->role === 'tata_usaha';
    }

    public function bisaCatatUntukOrangLain(): bool
    {
        return $this->isAdmin() || $this->isTataUsaha();
    }

    public function bisaBertindakSebagaiKepalaBalai(): bool
    {
        return $this->isKepalaBalai() || $this->is_plh_kepala_balai;
    }

    public static function kepalaBalai(): ?self
    {
        return static::where('role', 'atasan')->first();
    }

    public function perluAtasanLangsung(): bool
    {
        return ! $this->isKepalaBalai();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABEL[$this->role] ?? ucfirst((string) $this->role);
    }

    public function punyaTandaTangan(): bool
    {
        return $this->tanda_tangan
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->tanda_tangan);
    }

    public function getTandaTanganUrlAttribute(): ?string
    {
        return $this->punyaTandaTangan()
            ? Storage::disk('public')->url(str_replace('\\', '/', $this->tanda_tangan))
            : null;
    }

    public function getTandaTanganSkalaAmanAttribute(): int
    {
        $skala = (int) ($this->tanda_tangan_skala ?: self::TTD_SKALA_DEFAULT);

        return max(self::TTD_SKALA_MIN, min(self::TTD_SKALA_MAX, $skala));
    }

    public function tandaTanganTinggiPx(): float
    {
        return round(self::TTD_TINGGI_DASAR * $this->tanda_tangan_skala_aman / 100, 1);
    }

    public function tandaTanganTinggiMm(): float
    {
        return round($this->tandaTanganTinggiPx() / 96 * 25.4, 1);
    }

    public function tandaTanganDataUri(): ?string
    {
        if (! $this->punyaTandaTangan()) {
            return null;
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $isi  = $disk->get($this->tanda_tangan);
        $tipe = $disk->mimeType($this->tanda_tangan) ?: 'image/png';

        return 'data:' . $tipe . ';base64,' . base64_encode($isi);
    }

    public function getNipFormattedAttribute(): string
    {
        $nip = (string) $this->nip;

        if (strlen($nip) !== 18) {
            return $nip;
        }

        return substr($nip, 0, 8) . ' ' . substr($nip, 8, 6) . ' '
            . substr($nip, 14, 1) . ' ' . substr($nip, 15, 3);
    }

    public function getMasaKerjaAttribute(): string
    {
        if (! $this->tmt_pns) {
            return '-';
        }

        $selisih = $this->tmt_pns->diff(now());

        return $selisih->y . ' Tahun ' . $selisih->m . ' Bulan';
    }

    public function masaKerjaMinimal(int $tahun): bool
    {
        return $this->tmt_pns !== null && $this->tmt_pns->diffInYears(now()) >= $tahun;
    }
}
