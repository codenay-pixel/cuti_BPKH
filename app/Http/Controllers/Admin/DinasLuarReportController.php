<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeEvent;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        $perGrup = 15;
        $halaman = (int) $request->get('page', 1);

        // Dikelompokkan per pegawai, bukan per baris kegiatan -- satu pegawai
        // cuma muncul sekali di tabel, dan semua kegiatannya disatukan di
        // dalam grup itu supaya bisa dibuka/tutup dari nama pegawainya.
        $semuaGrup = $query->orderByDesc('tanggal_mulai')->get()
            ->groupBy('user_id')
            ->map(function ($rows) {
                return [
                    'user'            => $rows->first()->user,
                    'kegiatan'        => $rows,
                    'jumlah_kegiatan' => $rows->count(),
                    'total_hari'      => $rows->sum->lama_hari,
                ];
            })
            ->sortBy(fn ($grup) => $grup['user']->name ?? '')
            ->values();

        $riwayat = new LengthAwarePaginator(
            $semuaGrup->forPage($halaman, $perGrup)->values(),
            $semuaGrup->count(),
            $perGrup,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.dinas-luar.index', [
            'riwayat'          => $riwayat,
            'totalKegiatan'    => $semuaGrup->sum('jumlah_kegiatan'),
            'tahun'            => $tahun,
            'bulan'            => $bulan,
            'jenis'            => $jenis,
            'jenisOptions'     => OfficeEvent::JENIS,
            'tahunTersedia'    => $this->tahunTersedia(),
            'tahunIniBerjalan' => $tahun === now()->year,
        ]);
    }
}
