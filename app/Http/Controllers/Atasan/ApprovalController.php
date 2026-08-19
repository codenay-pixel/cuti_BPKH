<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $pengajuan = LeaveRequest::where('current_approver_id', auth()->id())
            ->where('status', 'menunggu')
            ->with(['user', 'leaveType'])
            ->latest()
            ->get();

        return view('atasan.approval.index', compact('pengajuan'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->current_approver_id !== auth()->id(), 403);

        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => auth()->id(),
            'level' => 'atasan_langsung',
            'keputusan' => 'disetujui',
            'catatan' => $request->catatan,
            'tanggal_keputusan' => now(),
        ]);

        $kepalaBalai = User::where('role', 'atasan')->first();

        $leaveRequest->update([
            'status' => 'disetujui_atasan',
            'current_approver_id' => $kepalaBalai?->id,
        ]);

        return back()->with('success', 'Cuti disetujui, diteruskan ke Kepala Balai.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->current_approver_id !== auth()->id(), 403);

        $request->validate(['catatan' => 'required|string']);

        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => auth()->id(),
            'level' => 'atasan_langsung',
            'keputusan' => 'ditolak',
            'catatan' => $request->catatan,
            'tanggal_keputusan' => now(),
        ]);

        $leaveRequest->update(['status' => 'ditolak']);

        return back()->with('success', 'Cuti ditolak.');
    }
}