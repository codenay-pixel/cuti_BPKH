<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    protected $fillable = [
        'leave_request_id',
        'approver_id',
        'level',
        'keputusan',
        'catatan',
        'tanggal_keputusan',
        'sebagai_plh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keputusan' => 'datetime',
            'sebagai_plh' => 'boolean',
        ];
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * withTrashed() supaya jejak persetujuan lama (mis. di cetak formulir
     * cuti) tetap tampilkan nama & tanda tangan penyetuju walau akunnya
     * sudah dinonaktifkan (soft delete).
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id')->withTrashed();
    }
}
