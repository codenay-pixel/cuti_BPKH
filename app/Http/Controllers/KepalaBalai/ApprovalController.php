<?php

namespace App\Http\Controllers\KepalaBalai;

use App\Http\Controllers\Controller;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    /**
     * Antrean & riwayat ini SELALU milik akun Kepala Balai yang sesungguhnya
     * (current_approver_id mengarah ke situ, bukan ke Plh) -- supaya Kepala
     * Balai dan Plh-nya melihat antrean yang sama persis, siapa pun yang
     * sedang login. Lihat User::bisaBertindakSebagaiKepalaBalai().
     */
    public function index(Request $request)
    {
        $kepalaBalai = User::kepalaBalai();

        $pengajuan = LeaveRequest::where('current_approver_id', $kepalaBalai?->id)
            ->whereIn('status', ['menunggu', 'disetujui_atasan'])
            ->with(['user', 'leaveType', 'approvals.approver'])
            ->latest()
            ->get();

        $riwayat = LeaveRequest::whereHas('approvals', fn ($q) => $q->where('level', 'kepala_balai'))
            ->with(['user', 'leaveType', 'approvals.approver'])
            ->latest()
            ->limit(20)
            ->get();

        return view('kepala-balai.approval.index', compact('pengajuan', 'riwayat'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $kepalaBalai = User::kepalaBalai();
        abort_if(! $kepalaBalai || $leaveRequest->current_approver_id !== $kepalaBalai->id, 403);

        try {
            $this->leaveService->setujuiCutiFinal($leaveRequest);

            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id'      => $request->user()->id,
                'level'            => 'kepala_balai',
                'keputusan'        => 'disetujui',
                'catatan'          => $request->catatan,
                'tanggal_keputusan' => now(),
                'sebagai_plh'      => (bool) $request->user()->is_plh_kepala_balai,
            ]);

            $leaveRequest->update(['current_approver_id' => null]);

            return back()->with('success', 'Cuti disetujui. Formulir sudah dapat dicetak oleh pegawai.');
        } catch (\Exception $e) {
            return back()->withErrors(['saldo' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $kepalaBalai = User::kepalaBalai();
        abort_if(! $kepalaBalai || $leaveRequest->current_approver_id !== $kepalaBalai->id, 403);

        $request->validate(
            ['catatan' => 'required|string|max:500'],
            ['catatan.required' => 'Alasan penolakan wajib diisi.']
        );

        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id'      => $request->user()->id,
            'level'            => 'kepala_balai',
            'keputusan'        => 'ditolak',
            'catatan'          => $request->catatan,
            'tanggal_keputusan' => now(),
            'sebagai_plh'      => (bool) $request->user()->is_plh_kepala_balai,
        ]);

        $leaveRequest->update(['status' => 'ditolak', 'current_approver_id' => null]);

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }
}
