<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'nama_cuti',
        'jatah_hari_default',
        'perlu_lampiran',
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
}