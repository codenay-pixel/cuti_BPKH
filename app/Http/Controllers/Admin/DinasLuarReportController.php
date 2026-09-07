<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeEvent;
use Illuminate\Http\Request;

class DinasLuarReportController extends Controller
{
    /**
     * Tahun-tahun yang punya data Dinas Luar, terbaru lebih dulu.
     * Tahun berjalan selalu disertakan walau belum ada datanya.
     */
    protected function tahunTersedia(): array
    {
        return OfficeEvent::dinasLuar()
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

        $query = OfficeEvent::with(['user', 'dicatatOleh'])
            ->dinasLuar()
            ->whereYear('tanggal_mulai', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
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
            'riwayat'         => $riwayat,
            'rekap'           => $rekap,
            'tahun'           => $tahun,
            'bulan'           => $bulan,
            'tahunTersedia'   => $this->tahunTersedia(),
            'tahunIniBerjalan' => $tahun === now()->year,
        ]);
    }
}
