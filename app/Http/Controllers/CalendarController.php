<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        // Ambil semua cuti yang disetujui dan overlap dengan bulan ini
        $cutiBulanIni = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $akhirBulan)
            ->where('tanggal_selesai', '>=', $awalBulan)
            ->get();

        // Kelompokkan per tanggal untuk ditampilkan di kalender
        $cutiPerTanggal = [];
        foreach ($cutiBulanIni as $cuti) {
            $periode = Carbon::parse($cuti->tanggal_mulai)->daysUntil($cuti->tanggal_selesai);
            foreach ($periode as $tanggal) {
                if ($tanggal->between($awalBulan, $akhirBulan)) {
                    $key = $tanggal->format('Y-m-d');
                    $cutiPerTanggal[$key][] = $cuti;
                }
            }
        }

        // Yang sedang cuti hari ini
        $sedangCutiHariIni = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->get();

        return view('calendar.index', compact(
            'awalBulan',
            'akhirBulan',
            'cutiPerTanggal',
            'sedangCutiHariIni',
            'bulan',
            'tahun'
        ));
    }
}