<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function hitungHariKerja(string $mulai, string $selesai): int
    {
        $period = CarbonPeriod::create($mulai, $selesai);
        $hariKerja = 0;

        foreach ($period as $tanggal) {
            if (!$tanggal->isWeekend()) {
                $hariKerja++;
            }
        }

        return $hariKerja;
    }

    public function ajukanCuti(array $data, $user, ?UploadedFile $lampiran = null): LeaveRequest
    {
        $jumlahHari = $this->hitungHariKerja($data['tanggal_mulai'], $data['tanggal_selesai']);
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        if ($leaveType->mengurangi_saldo) {
            $balance = LeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->where('tahun', now()->year)
                ->first();

            if (!$balance || $balance->sisa < $jumlahHari) {
                throw new \Exception('Sisa cuti tidak mencukupi. Sisa saat ini: ' . ($balance->sisa ?? 0) . ' hari.');
            }
        }

        $lampiranPath = null;
        if ($lampiran) {
            $lampiranPath = $lampiran->store('lampiran-cuti', 'public');
        }

        return DB::transaction(function () use ($data, $user, $jumlahHari, $lampiranPath) {
            return LeaveRequest::create([
                'user_id' => $user->id,
                'leave_type_id' => $data['leave_type_id'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'jumlah_hari' => $jumlahHari,
                'alasan' => $data['alasan'],
                'lampiran' => $lampiranPath,
                'status' => 'menunggu',
                'current_approver_id' => $user->atasan_id,
            ]);
        });
    }

    public function setujuiCutiFinal(LeaveRequest $leaveRequest): void
    {
        DB::transaction(function () use ($leaveRequest) {
            if (!$leaveRequest->leaveType->mengurangi_saldo) {
                $leaveRequest->update(['status' => 'disetujui']);
                return;
            }

            $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('tahun', now()->year)
                ->lockForUpdate()
                ->first();

            if (!$balance || $balance->sisa < $leaveRequest->jumlah_hari) {
                throw new \Exception('Sisa cuti tidak mencukupi saat finalisasi.');
            }

            $balance->increment('terpakai', $leaveRequest->jumlah_hari);

            $leaveRequest->update(['status' => 'disetujui']);
        });
    }
}