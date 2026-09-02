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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}