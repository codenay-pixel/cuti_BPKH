<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\OfficeEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }

        $awalBulan  = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        // Ambil rentang penuh yang tampil di grid (termasuk sisa bulan sebelah),
        // supaya tanggal di baris pertama dan terakhir juga bisa diklik.
        $awalGrid  = $awalBulan->copy()->startOfWeek(Carbon::SUNDAY);
        $akhirGrid = $akhirBulan->copy()->endOfWeek(Carbon::SATURDAY);

        $agenda = [];

        $cuti = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $akhirGrid)
            ->whereDate('tanggal_selesai', '>=', $awalGrid)
            ->get();

        foreach ($cuti as $c) {
            foreach (Carbon::parse($c->tanggal_mulai)->daysUntil($c->tanggal_selesai) as $t) {
                if (! $t->betweenIncluded($awalGrid, $akhirGrid)) {
                    continue;
                }

                $agenda[$t->format('Y-m-d')][] = [
                    'tipe'     => 'cuti',
                    'nama'     => $c->user->name,
                    'judul'    => $c->leaveType->nama_cuti,
                    'ket'      => 'Cuti ' . $c->tanggal_mulai->translatedFormat('d M')
                                  . ' s/d ' . $c->tanggal_selesai->translatedFormat('d M Y'),
                    'lampiran' => null,
                ];
            }
        }

        $acara = OfficeEvent::with('user')
            ->whereDate('tanggal_mulai', '<=', $akhirGrid)
            ->whereDate('tanggal_selesai', '>=', $awalGrid)
            ->get();

        foreach ($acara as $a) {
            foreach (Carbon::parse($a->tanggal_mulai)->daysUntil($a->tanggal_selesai) as $t) {
                if (! $t->betweenIncluded($awalGrid, $akhirGrid)) {
                    continue;
                }

                $agenda[$t->format('Y-m-d')][] = [
                    'tipe'     => 'acara',
                    'nama'     => $a->user->name,
                    'judul'    => $a->nama_acara,
                    'ket'      => trim(($a->lokasi ? $a->lokasi . ' · ' : '') . $a->jenis_label
                                  . ' · ' . $a->tanggal_mulai->translatedFormat('d M')
                                  . ' s/d ' . $a->tanggal_selesai->translatedFormat('d M Y')),
                    'lampiran' => $a->lampiran_url,
                ];
            }
        }

        ksort($agenda);

        // Tanggal yang dipilih saat halaman dibuka: hari ini bila bulan yang
        // ditampilkan adalah bulan berjalan, selain itu tanggal 1.
        $tanggalAwal = $awalBulan->isSameMonth(now())
            ? now()->format('Y-m-d')
            : $awalBulan->format('Y-m-d');

        $acaraMendatang = OfficeEvent::with('user')
            ->whereDate('tanggal_selesai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->limit(10)
            ->get();

        return view('calendar.index', compact(
            'awalBulan', 'akhirBulan', 'awalGrid', 'akhirGrid',
            'agenda', 'tanggalAwal', 'acaraMendatang', 'bulan', 'tahun'
        ));
    }
}
