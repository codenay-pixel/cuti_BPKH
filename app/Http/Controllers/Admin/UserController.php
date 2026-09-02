<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
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

            // NIP sering ditulis berspasi (19900303 201001 2 003). Angkanya
            // dipisahkan supaya pencarian tetap ketemu walau formatnya berbeda.
            $angka = preg_replace('/[^0-9]/', '', $cari);

            $query->where(function ($q) use ($cari, $angka) {
                $q->where('name', 'like', "%{$cari}%")
                  ->orWhere('jabatan', 'like', "%{$cari}%")
                  ->orWhere('unit_kerja', 'like', "%{$cari}%")
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

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        unset($data['tanda_tangan'], $data['hapus_tanda_tangan']);

        // Slider ukuran tanda tangan tidak selalu ikut terkirim (mis. peran
        // Pegawai). Kolomnya NOT NULL, jadi nilai kosong dilewati saja.
        if (($data['tanda_tangan_skala'] ?? null) === null) {
            unset($data['tanda_tangan_skala']);
        }

        // Plh Kepala Balai: hanya berlaku untuk peran Atasan Langsung, dan
        // cuma boleh satu orang aktif dalam satu waktu.
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
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['tanda_tangan'], $data['hapus_tanda_tangan']);

        // Slider ukuran tanda tangan tidak selalu ikut terkirim (mis. peran
        // Pegawai). Kolomnya NOT NULL, jadi nilai kosong dilewati saja.
        if (($data['tanda_tangan_skala'] ?? null) === null) {
            unset($data['tanda_tangan_skala']);
        }

        // Plh Kepala Balai: hanya berlaku untuk peran Atasan Langsung, dan
        // cuma boleh satu orang aktif dalam satu waktu -- mencentang untuk
        // satu pegawai otomatis melepas status Plh pegawai lain.
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

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        if ($user->tanda_tangan) {
            Storage::disk('public')->delete($user->tanda_tangan);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}