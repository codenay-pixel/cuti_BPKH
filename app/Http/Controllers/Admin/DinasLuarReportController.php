<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeEvent;
use Illuminate\Http\Request;

class DinasLuarReportController extends Controller
{
    protected function tahunTersedia(): array
    {
        return OfficeEvent::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_mulai) as tahun')
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
        $bulan = $request->filled('bulan') ? (int) $request->bulan : null;
        $jenis = $request->filled('jenis') ? $request->jenis : null;

        $query = OfficeEvent::with(['user', 'dicatatOleh'])
            ->whereYear('tanggal_mulai', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }

        if ($jenis && array_key_exists($jenis, OfficeEvent::JENIS)) {
            $query->where('jenis', $jenis);
        }

        if ($request->filled('nama')) {
            $nama = mb_strtolower(trim($request->nama));
            $query->whereHas('user', function ($q) use ($nama) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$nama}%"]);
            });
        }

        // Rekap per pegawai dihitung dari seluruh data yang cocok filter
        // (sebelum dipaginasi), supaya totalnya tidak terpotong halaman.
        $rekap = (clone $query)->get()
            ->groupBy('user_id')
            ->map(function ($rows) {
                return [
                    'user'            => $rows->first()->user,
                    'jumlah_kegiatan' => $rows->count(),
                    'total_hari'      => $rows->sum->lama_hari,
                ];
            })
            ->sortByDesc('total_hari')
            ->values();

        $riwayat = $query->orderByDesc('tanggal_mulai')->paginate(15)->withQueryString();

        return view('admin.dinas-luar.index', [
            'riwayat'          => $riwayat,
            'rekap'            => $rekap,
            'tahun'            => $tahun,
            'bulan'            => $bulan,
            'jenis'            => $jenis,
            'jenisOptions'     => OfficeEvent::JENIS,
            'tahunTersedia'    => $this->tahunTersedia(),
            'tahunIniBerjalan' => $tahun === now()->year,
        ]);
    }
}
