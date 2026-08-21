<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\OfficeEvent;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();

        $this->leaveService->pastikanSaldoTahunan($user);
        $saldo = $this->leaveService->rincianSaldoTahunan($user);

        $pengajuanSaya = LeaveRequest::where('user_id', $user->id)
            ->with('leaveType')
            ->latest()
            ->limit(5)
            ->get();

        $menungguSaya = 0;
        if ($user->isAtasanLangsung()) {
            $menungguSaya = LeaveRequest::where('current_approver_id', $user->id)
                ->where('status', 'menunggu')->count();
        } elseif ($user->isKepalaBalai()) {
            $menungguSaya = LeaveRequest::where('current_approver_id', $user->id)
                ->whereIn('status', ['menunggu', 'disetujui_atasan'])->count();
        }

        $sedangCuti = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->get();

        $sedangDinas = OfficeEvent::with('user')
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->get();

        return view('dashboard', compact(
            'saldo', 'pengajuanSaya', 'menungguSaya', 'sedangCuti', 'sedangDinas'
        ));
    }
}
