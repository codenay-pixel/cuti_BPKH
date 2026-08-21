<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LeaveReportExport;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LeaveReportController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    /**
     * Hapus satu pengajuan cuti beserta jejak persetujuan dan lampirannya.
     * Bila pengajuan sudah disetujui, saldo cuti tahunan yang terpotong
     * dikembalikan lebih dulu agar hitungannya tidak melenceng.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $nama = $leaveRequest->user->name;
        $dikembalikan = $this->leaveService->kembalikanSaldo($leaveRequest);

        if ($leaveRequest->lampiran) {
            Storage::disk('public')->delete($leaveRequest->lampiran);
        }

        $leaveRequest->delete();

        $pesan = 'Pengajuan cuti ' . $nama . ' berhasil dihapus.';

        if ($dikembalikan > 0) {
            $pesan .= ' Saldo cuti tahunan dikembalikan ' . $dikembalikan . ' hari.';
        }

        return back()->with('success', $pesan);
    }

    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'leaveType'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nama')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        $riwayat = $query->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('riwayat'));
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new LeaveReportExport($request->status, $request->nama),
            'rekap-cuti-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $query = LeaveRequest::with(['user', 'leaveType'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nama')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        $riwayat = $query->get();

        $pdf = Pdf::loadView('admin.reports.pdf', compact('riwayat'));

        return $pdf->download('rekap-cuti-' . now()->format('Y-m-d') . '.pdf');
    }
}