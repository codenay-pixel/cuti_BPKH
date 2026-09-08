<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('atasan')->orderBy('name');

        if ($request->filled('cari')) {
            $cari = trim($request->cari);

            $angka = preg_replace('/[^0-9]/', '', $cari);

            $cariLower = mb_strtolower($cari);

            $query->where(function ($q) use ($cariLower, $cari, $angka) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$cariLower}%"])
                  ->orWhereRaw('LOWER(jabatan) LIKE ?', ["%{$cariLower}%"])
                  ->orWhereRaw('LOWER(unit_kerja) LIKE ?', ["%{$cariLower}%"])
                  ->orWhere('nip', 'like', "%{$cari}%");

                if ($angka !== '') {
                    $q->orWhere('nip', 'like', "%{$angka}%");
                }
            });
        }

        if ($request->filled('peran')) {
            $query->where('role', $request->peran);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $atasanList = User::whereIn('role', ['atasan_langsung', 'atasan'])->orderBy('name')->get();

        return view('admin.users.create', compact('atasanList'));
    }

    /**
     * Kartu profil pegawai: data diri plus riwayat Dinas Luar-nya, supaya
     * Tata Usaha/Admin bisa cek riwayat SPT seseorang tanpa harus menyaring
     * dari daftar rekap.
     */
    public function show(User $user)
    {
        $tahun = now()->year;
        $bulanIni = now()->month;

        $riwayatDinasLuar = $user->officeEvents()
            ->dinasLuar()
            ->orderByDesc('tanggal_mulai')
            ->limit(10)
            ->get();

        $dinasLuarBulanIni = $user->officeEvents()
            ->dinasLuar()
            ->whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulanIni)
            ->get();

        return view('admin.users.show', [
            'user'              => $user,
            'riwayatDinasLuar'  => $riwayatDinasLuar,
            'jumlahBulanIni'    => $dinasLuarBulanIni->count(),
            'totalHariBulanIni' => $dinasLuarBulanIni->sum->lama_hari,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        unset($data['tanda_tangan'], $data['hapus_tanda_tangan']);

        if (($data['tanda_tangan_skala'] ?? null) === null) {
            unset($data['tanda_tangan_skala']);
        }

        $jadiPlh = $request->boolean('is_plh_kepala_balai') && ($data['role'] ?? null) === 'atasan_langsung';
        unset($data['is_plh_kepala_balai']);

        $user = DB::transaction(function () use ($data, $jadiPlh) {
            if ($jadiPlh) {
                User::where('is_plh_kepala_balai', true)->update(['is_plh_kepala_balai' => false]);
            }

            $data['is_plh_kepala_balai'] = $jadiPlh;

            return User::create($data);
        });

        $this->simpanTandaTangan($user, $request->file('tanda_tangan'), false);

        Audit::catat('pegawai.ditambahkan', [
            'pegawai_id' => $user->id,
            'nama'       => $user->name,
            'role'       => $user->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $atasanList = User::whereIn('role', ['atasan_langsung', 'atasan'])
            ->where('id', '!=', $user->id)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'atasanList'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $roleSebelumnya = $user->role;

        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['tanda_tangan'], $data['hapus_tanda_tangan']);

        if (($data['tanda_tangan_skala'] ?? null) === null) {
            unset($data['tanda_tangan_skala']);
        }

        $jadiPlh = $request->boolean('is_plh_kepala_balai') && ($data['role'] ?? $user->role) === 'atasan_langsung';
        unset($data['is_plh_kepala_balai']);

        DB::transaction(function () use ($user, $data, $jadiPlh) {
            if ($jadiPlh) {
                User::where('id', '!=', $user->id)
                    ->where('is_plh_kepala_balai', true)
                    ->update(['is_plh_kepala_balai' => false]);
            }

            $data['is_plh_kepala_balai'] = $jadiPlh;

            $user->update($data);
        });

        $this->simpanTandaTangan(
            $user,
            $request->file('tanda_tangan'),
            $request->boolean('hapus_tanda_tangan')
        );

        Audit::catat('pegawai.diubah', array_filter([
            'pegawai_id'      => $user->id,
            'nama'            => $user->name,
            'role_sebelumnya' => $roleSebelumnya !== $user->role ? $roleSebelumnya : null,
            'role_baru'       => $roleSebelumnya !== $user->role ? $user->role : null,
        ], fn ($v) => $v !== null));

        return redirect()->route('admin.users.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Simpan / ganti / hapus gambar tanda tangan pejabat.
     * Berkas lama selalu dibuang supaya folder tidak menumpuk file yatim.
     */
    protected function simpanTandaTangan(User $user, ?UploadedFile $berkas, bool $hapus): void
    {
        if (! $berkas && ! $hapus) {
            return;
        }

        if ($user->tanda_tangan) {
            Storage::disk('public')->delete($user->tanda_tangan);
        }

        $path = $berkas
            ? $berkas->store('tanda-tangan', 'public')
            : null;

        $user->forceFill(['tanda_tangan' => $path])->save();
    }

    /**
     * Menghapus pegawai sebenarnya menonaktifkan akunnya (soft delete) --
     * berkas tanda tangan SENGAJA tidak ikut dibuang, karena masih dipakai
     * kalau ada formulir cuti lama miliknya yang dicetak ulang. Riwayat cuti
     * dan Dinas Luar/kegiatan atas namanya juga tetap tersimpan di laporan.
     */
    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        Audit::catat('pegawai.dihapus', [
            'pegawai_id' => $user->id,
            'nama'       => $user->name,
            'role'       => $user->role,
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pegawai berhasil dihapus. Akunnya dinonaktifkan -- riwayat cuti dan kegiatannya tetap tersimpan di laporan.');
    }
}