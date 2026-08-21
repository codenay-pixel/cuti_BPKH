<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        ];
    }

    public const ROLE_LABEL = [
        'pegawai'         => 'Pegawai',
        'atasan_langsung' => 'Atasan Langsung',
        'atasan'          => 'Kepala Balai',
        'admin'           => 'Admin Kepegawaian',
    ];

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
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

    /** Semua peran yang bertindak sebagai pemberi persetujuan. */
    public function isAtasan(): bool
    {
        return in_array($this->role, ['atasan_langsung', 'atasan'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kepala Balai berada di puncak rantai persetujuan, jadi ia boleh tidak
     * punya atasan langsung — pengajuan cutinya disetujui sendiri. Peran lain
     * wajib punya atasan agar cutinya ada tujuan persetujuan.
     */
    public function perluAtasanLangsung(): bool
    {
        return ! $this->isKepalaBalai();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABEL[$this->role] ?? ucfirst((string) $this->role);
    }

    /** Pejabat sudah mengunggah gambar tanda tangan? */
    public function punyaTandaTangan(): bool
    {
        return $this->tanda_tangan
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->tanda_tangan);
    }

    /** URL gambar tanda tangan untuk ditampilkan di halaman web. */
    public function getTandaTanganUrlAttribute(): ?string
    {
        return $this->punyaTandaTangan()
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->tanda_tangan)
            : null;
    }

    /**
     * Gambar tanda tangan dalam bentuk data URI.
     * DomPDF tidak selalu bisa membaca berkas lewat URL, jadi untuk PDF
     * gambarnya disisipkan langsung sebagai base64.
     */
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

    /** NIP diformat: 19900303 201001 2 003 */
    public function getNipFormattedAttribute(): string
    {
        $nip = (string) $this->nip;

        if (strlen($nip) !== 18) {
            return $nip;
        }

        return substr($nip, 0, 8) . ' ' . substr($nip, 8, 6) . ' '
            . substr($nip, 14, 1) . ' ' . substr($nip, 15, 3);
    }

    /** Masa kerja dihitung dari TMT PNS, contoh: "8 Tahun 3 Bulan". */
    public function getMasaKerjaAttribute(): string
    {
        if (! $this->tmt_pns) {
            return '-';
        }

        $selisih = $this->tmt_pns->diff(now());

        return $selisih->y . ' Tahun ' . $selisih->m . ' Bulan';
    }

    /** Sudah bekerja minimal N tahun terus-menerus? (syarat cuti besar / CLTN) */
    public function masaKerjaMinimal(int $tahun): bool
    {
        return $this->tmt_pns !== null && $this->tmt_pns->diffInYears(now()) >= $tahun;
    }
}
