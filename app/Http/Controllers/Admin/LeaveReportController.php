<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LeaveReportExport;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\LeaveService;
use App\Support\Audit;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LeaveReportController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $nama = $leaveRequest->user->name;
        $dikembalikan = $this->leaveService->kembalikanSaldo($leaveRequest);

        if ($leaveRequest->lampiran) {
            Storage::disk('public')->delete($leaveRequest->lampiran);
        }

        Audit::catat('cuti.dihapus_admin', [
            'leave_request_id' => $leaveRequest->id,
            'pegawai_id'       => $leaveRequest->user_id,
            'nama'             => $nama,
            'saldo_dikembalikan' => $dikembalikan,
        ]);

        $leaveRequest->delete();

        $pesan = 'Pengajuan cuti ' . $nama . ' berhasil dihapus.';

        if ($dikembalikan > 0) {
            $pesan .= ' Saldo cuti tahunan dikembalikan ' . $dikembalikan . ' hari.';
        }

        return back()->with('success', $pesan);
    }

    protected function tahunTersedia(): array
    {
        return LeaveRequest::selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : now()->year;

        $query = LeaveRequest::with(['user', 'leaveType'])
            ->whereYear('tanggal_mulai', $tahun)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nama')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        $riwayat = $query->paginate(15)->withQueryString();

        return view('admin.reports.index', [
            'riwayat' => $riwayat,
            'tahun' => $tahun,
            'tahunTersedia' => $this->tahunTersedia(),
            'tahunIniBerjalan' => $tahun === now()->year,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : now()->year;

        $namaFile = $tahun === now()->year
            ? 'rekap-cuti-' . now()->format('Y-m-d') . '.xlsx'
            : 'rekap-cuti-arsip-' . $tahun . '.xlsx';

        return Excel::download(
            new LeaveReportExport($tahun, $request->status, $request->nama),
            $namaFile
        );
    }

    public function exportPdf(Request $request)
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : now()->year;

        $query = LeaveRequest::with(['user', 'leaveType'])
            ->whereYear('tanggal_mulai', $tahun)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nama')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        $riwayat = $query->get();

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'riwayat' => $riwayat,
            'tahun' => $tahun,
            'tahunIniBerjalan' => $tahun === now()->year,
        ]);

        $namaFile = $tahun === now()->year
            ? 'rekap-cuti-' . now()->format('Y-m-d') . '.pdf'
            : 'rekap-cuti-arsip-' . $tahun . '.pdf';

        return $pdf->download($namaFile);
    }
}
