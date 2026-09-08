<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'alamat_cuti',
        'telepon_cuti',
        'nomor_surat',
        'lampiran',
        'status',
        'current_approver_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public const STATUS_LABEL = [
        'menunggu'          => 'Menunggu Atasan Langsung',
        'disetujui_atasan'  => 'Menunggu Pejabat Pemberi Cuti',
        'disetujui'         => 'Disetujui',
        'ditolak'           => 'Ditolak',
    ];

    public function getLampiranUrlAttribute(): ?string
    {
        return $this->lampiran
            ? Storage::disk('public')->url(str_replace('\\', '/', $this->lampiran))
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id')->withTrashed();
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class);
    }

    public function approvalAtasanLangsung(): ?LeaveApproval
    {
        return $this->approvals->firstWhere('level', 'atasan_langsung');
    }

    public function approvalKepalaBalai(): ?LeaveApproval
    {
        return $this->approvals->firstWhere('level', 'kepala_balai');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['disetujui', 'ditolak'], true);
    }

    public function bolehDiubah(): bool
    {
        return ! $this->isFinal() && $this->approvals->isEmpty();
    }

    public function sudahDisetujuiPenuh(): bool
    {
        return $this->status === 'disetujui';
    }
}
