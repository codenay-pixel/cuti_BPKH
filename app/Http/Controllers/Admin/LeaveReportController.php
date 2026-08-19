<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LeaveReportExport;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LeaveReportController extends Controller
{
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