<?php

namespace App\Http\Controllers\KepalaBalai;

use App\Http\Controllers\Controller;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function index()
    {
        $pengajuan = LeaveRequest::where('current_approver_id', auth()->id())
            ->where('status', 'disetujui_atasan')
            ->with(['user', 'leaveType'])
            ->latest()
            ->get();

        return view('kepala-balai.approval.index', compact('pengajuan'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->current_approver_id !== auth()->id(), 403);

        try {
            $this->leaveService->setujuiCutiFinal($leaveRequest);

            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id' => auth()->id(),
                'level' => 'kepala_balai',
                'keputusan' => 'disetujui',
                'catatan' => $request->catatan,
                'tanggal_keputusan' => now(),
            ]);

            return back()->with('success', 'Cuti disetujui final.');
        } catch (\Exception $e) {
            return back()->withErrors(['saldo' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->current_approver_id !== auth()->id(), 403);

        $request->validate(['catatan' => 'required|string']);

        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => auth()->id(),
            'level' => 'kepala_balai',
            'keputusan' => 'ditolak',
            'catatan' => $request->catatan,
            'tanggal_keputusan' => now(),
        ]);

        $leaveRequest->update(['status' => 'ditolak']);

        return back()->with('success', 'Cuti ditolak.');
    }
}