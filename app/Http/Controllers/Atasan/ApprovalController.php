<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = LeaveRequest::where('current_approver_id', $request->user()->id)
            ->where('status', 'menunggu')
            ->with(['user', 'leaveType'])
            ->latest()
            ->get();

        $riwayat = LeaveRequest::whereHas('approvals', fn ($q) => $q->where('approver_id', $request->user()->id))
            ->with(['user', 'leaveType', 'approvals.approver'])
            ->latest()
            ->limit(20)
            ->get();

        return view('atasan.approval.index', compact('pengajuan', 'riwayat'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->current_approver_id !== $request->user()->id, 403);

        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id'      => $request->user()->id,
            'level'            => 'atasan_langsung',
            'keputusan'        => 'disetujui',
            'catatan'          => $request->catatan,
            'tanggal_keputusan' => now(),
        ]);

        $pejabatPemberiCuti = $request->user()->atasan
            ?? User::where('role', 'atasan')->first();

        if (! $pejabatPemberiCuti) {
            return back()->withErrors([
                'approval' => 'Pejabat pemberi cuti (Kepala Balai) belum terdaftar. Hubungi admin kepegawaian.',
            ]);
        }

        $leaveRequest->update([
            'status' => 'disetujui_atasan',
            'current_approver_id' => $pejabatPemberiCuti->id,
        ]);

        Audit::catat('cuti.disetujui_atasan', [
            'leave_request_id' => $leaveRequest->id,
            'pegawai_id'       => $leaveRequest->user_id,
            'diteruskan_ke'    => $pejabatPemberiCuti->id,
        ]);

        return back()->with('success', 'Cuti disetujui dan diteruskan ke ' . $pejabatPemberiCuti->name . '.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->current_approver_id !== $request->user()->id, 403);

        $request->validate(
            ['catatan' => 'required|string|max:500'],
            ['catatan.required' => 'Alasan penolakan wajib diisi.']
        );

        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id'      => $request->user()->id,
            'level'            => 'atasan_langsung',
            'keputusan'        => 'ditolak',
            'catatan'          => $request->catatan,
            'tanggal_keputusan' => now(),
        ]);

        $leaveRequest->update(['status' => 'ditolak', 'current_approver_id' => null]);

        Audit::catat('cuti.ditolak_atasan', [
            'leave_request_id' => $leaveRequest->id,
            'pegawai_id'       => $leaveRequest->user_id,
            'catatan'          => $request->catatan,
        ]);

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }
}
