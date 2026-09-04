<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    /**
     * Satu pegawai = satu baris, berisi tahun berjalan dan dua tahun terakhir
     * sekaligus. Lebih ringkas daripada satu baris per tahun.
     */
    public function index(Request $request)
    {

        $tahun = now()->year;
        $jenis = $this->leaveService->jenisTahunan();

        $query = User::orderBy('name');

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$cari}%")
                ->orWhere('nip', 'like', "%{$cari}%"));
        }

        $users = $query->paginate(20)->withQueryString();

        if ($jenis) {
            $users->getCollection()->each(
                fn (User $u) => $this->leaveService->pastikanSaldoTahunan($u, $tahun)
            );
        }

        $saldo = collect();
        if ($jenis) {
            $saldo = LeaveBalance::where('leave_type_id', $jenis->id)
                ->whereIn('user_id', $users->pluck('id'))
                ->whereBetween('tahun', [$tahun - LeaveService::TAHUN_AKUMULASI, $tahun])
                ->get()
                ->groupBy('user_id');
        }

        $baris = $users->getCollection()->map(function (User $u) use ($saldo, $tahun) {
            return $this->ringkas($u, $saldo->get($u->id, collect()), $tahun);
        });

        return view('admin.leave-balances.index', compact('baris', 'users', 'tahun'));
    }

    /** Form pengaturan tiga tahun sekaligus untuk satu pegawai. */
    public function edit(User $user)
    {
        $tahun = now()->year;
        $jenis = $this->leaveService->jenisTahunan();

        abort_if(! $jenis, 404, 'Jenis Cuti Tahunan belum tersedia. Jalankan LeaveTypeSeeder terlebih dahulu.');

        $saldo = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $jenis->id)
            ->whereBetween('tahun', [$tahun - LeaveService::TAHUN_AKUMULASI, $tahun])
            ->get();

        $ringkasan = $this->ringkas($user, $saldo, $tahun);

        return view('admin.leave-balances.edit', compact('user', 'ringkasan', 'tahun'));
    }

    public function update(Request $request, User $user)
    {
        $jenis = $this->leaveService->jenisTahunan();
        abort_if(! $jenis, 404);

        $data = $request->validate([
            'tahun'              => ['required', 'array'],
            'tahun.*.jatah'      => ['required', 'integer', 'min:0', 'max:60'],
            'tahun.*.terpakai'   => ['required', 'integer', 'min:0', 'max:60'],
        ], [
            'tahun.*.jatah.required'    => 'Jatah wajib diisi.',
            'tahun.*.terpakai.required' => 'Terpakai wajib diisi.',
        ]);

        $batasBawah = now()->year - LeaveService::TAHUN_AKUMULASI;
        $batasAtas  = now()->year;

        foreach ($data['tahun'] as $th => $nilai) {

            if ((int) $th < $batasBawah || (int) $th > $batasAtas) {
                continue;
            }

            if ((int) $nilai['terpakai'] > (int) $nilai['jatah']) {
                return back()
                    ->withErrors(['tahun' => "Tahun {$th}: jumlah terpakai ({$nilai['terpakai']}) melebihi jatah ({$nilai['jatah']})."])
                    ->withInput();
            }

            LeaveBalance::updateOrCreate(
                ['user_id' => $user->id, 'leave_type_id' => $jenis->id, 'tahun' => (int) $th],
                ['jatah' => (int) $nilai['jatah'], 'terpakai' => (int) $nilai['terpakai']],
            );
        }

        return redirect()
            ->route('admin.leave-balances.index')
            ->with('success', 'Saldo cuti ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Susun tiga tahun (N-2, N-1, N) beserta berapa hari yang benar-benar
     * boleh dipakai pada tahun berjalan.
     */
    private function ringkas(User $u, $saldoUser, int $tahun): array
    {
        $rows = collect($saldoUser)->keyBy('tahun');
        $hasil = ['user' => $u, 'tahun' => [], 'total' => 0, 'ada_data' => false];

        for ($i = LeaveService::TAHUN_AKUMULASI; $i >= 0; $i--) {
            $t = $tahun - $i;
            $r = $rows->get($t);

            $jatah    = (int) ($r->jatah ?? 0);
            $terpakai = (int) ($r->terpakai ?? 0);
            $sisa     = max(0, $jatah - $terpakai);
            $tersedia = $i === 0 ? $sisa : min($sisa, LeaveService::MAKS_AKUMULASI);

            $hasil['tahun'][$t] = [
                'tahun'     => $t,
                'jatah'     => $jatah,
                'terpakai'  => $terpakai,
                'sisa'      => $sisa,
                'tersedia'  => $tersedia,
                'dibatasi'  => $i > 0 && $sisa > LeaveService::MAKS_AKUMULASI,
                'berjalan'  => $i === 0,
                'ada'       => $r !== null,
            ];

            $hasil['total'] += $tersedia;
            $hasil['ada_data'] = $hasil['ada_data'] || $r !== null;
        }

        return $hasil;
    }
}
