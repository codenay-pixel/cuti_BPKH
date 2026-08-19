<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function index()
    {
        $riwayat = LeaveRequest::where('user_id', auth()->id())
            ->with('leaveType')
            ->latest()
            ->paginate(10);

        return view('pegawai.leave.index', compact('riwayat'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::all();
        $saldo = LeaveBalance::where('user_id', auth()->id())
            ->where('tahun', now()->year)
            ->with('leaveType')
            ->get();

        return view('pegawai.leave.create', compact('leaveTypes', 'saldo'));
    }

    public function store(StoreLeaveRequest $request)
    {
        try {
            $this->leaveService->ajukanCuti(
                $request->validated(),
                auth()->user(),
                $request->file('lampiran')
            );

            return redirect()->route('leave.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
        } catch (\Exception $e) {
            return back()->withErrors(['saldo' => $e->getMessage()])->withInput();
        }
    }

    public function show(LeaveRequest $leaveRequest)
    {
        abort_if($leaveRequest->user_id !== auth()->id(), 403);

        $leaveRequest->load(['leaveType', 'approvals.approver']);

        return view('pegawai.leave.show', compact('leaveRequest'));
    }
}