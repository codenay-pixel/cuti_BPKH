<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
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

    /**
     * Pengajuan masih boleh diubah atau dibatalkan pemohonnya selama belum
     * ada satu pun keputusan tercatat. Memakai jejak persetujuan, bukan status,
     * agar pengajuan Kepala Balai yang menyetujui sendiri juga ikut tercakup.
     */
    public function bolehDiubah(): bool
    {
        return ! $this->isFinal() && $this->approvals->isEmpty();
    }

    /**
     * Formulir hanya boleh dicetak setelah disetujui atasan langsung DAN
     * pejabat pemberi cuti. Dipakai untuk menampilkan tombol di antarmuka;
     * penguncian sebenarnya ada di LeaveRequestController::cetak().
     */
    public function sudahDisetujuiPenuh(): bool
    {
        return $this->status === 'disetujui';
    }
}
