<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Pegawai</h2>
                <p class="text-sm text-gray-500 mt-0.5">Akun login, peran, dan atasan langsung</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center gap-1.5 bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 w-full sm:w-auto">
                <span class="text-lg leading-none">+</span> Tambah Pegawai
            </a>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">

                {{-- Pencarian --}}
                <form method="GET" class="grid grid-cols-2 sm:flex sm:flex-wrap sm:items-end gap-3 px-4 sm:px-5 py-4 border-b border-gray-300 bg-gray-50">
                    <div class="col-span-2 sm:col-auto">
                        <label for="cari" class="block text-[11px] font-medium text-gray-500 mb-1">Cari Pegawai</label>
                        <input type="text" id="cari" name="cari" value="{{ request('cari') }}"
                               placeholder="Nama, NIP, jabatan, atau unit kerja..."
                               class="w-full sm:w-72 rounded-lg border-gray-300 text-sm py-1.5">
                    </div>

                    <div class="col-span-2 sm:col-auto">
                        <label for="peran" class="block text-[11px] font-medium text-gray-500 mb-1">Peran</label>
                        <select id="peran" name="peran" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5 pe-8">
                            <option value="">Semua peran</option>
                            @foreach (\App\Models\User::ROLE_LABEL as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(request('peran') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="px-4 py-1.5 rounded-lg bg-gray-800 text-white text-sm hover:bg-gray-700">Cari</button>

                    @if (request()->hasAny(['cari', 'peran']))
                        <a href="{{ route('admin.users.index') }}"
                           class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800 text-center">Reset</a>
                    @endif

                    <div class="col-span-2 sm:ms-auto text-xs text-gray-500 sm:self-center">
                        {{ $users->total() }} pegawai
                        @if (request()->hasAny(['cari', 'peran'])) ditemukan @endif
                    </div>
                </form>

                {{-- Kartu untuk HP --}}
                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($users as $user)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $user->nip_formatted }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-md text-[11px] bg-gray-100 text-gray-700">{{ $user->role_label }}</span>
                            </div>
                            @if ($user->is_plh_kepala_balai)
                                <span class="inline-block px-2 py-1 rounded-md text-[11px] bg-amber-100 text-amber-800 font-medium">
                                    Sedang jadi Plh Kepala Balai
                                </span>
                            @endif

                            <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-gray-400">Jabatan</dt>
                                    <dd class="text-gray-800">{{ $user->jabatan ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Unit Kerja</dt>
                                    <dd class="text-gray-800">{{ $user->unit_kerja ?? '—' }}</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-gray-400">Atasan Langsung</dt>
                                    <dd class="text-gray-800">
                                        {{ $user->atasan->name ?? '—' }}
                                        @if (! $user->atasan_id && $user->perluAtasanLangsung())
                                            <span class="block text-[11px] text-amber-600">belum diatur, cuti tidak bisa diajukan</span>
                                        @elseif (! $user->atasan_id)
                                            <span class="block text-[11px] text-gray-400">menyetujui cutinya sendiri</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <div class="flex gap-2 pt-1">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="flex-1 text-center px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-700">Ubah</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="flex-1"
                                          onsubmit="return confirm('Hapus pegawai {{ $user->name }}? Seluruh riwayat cutinya ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="w-full px-3 py-2 rounded-lg border border-rose-300 text-xs text-rose-600">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <p class="text-sm text-gray-500">
                                {{ request()->hasAny(['cari', 'peran'])
                                    ? 'Tidak ada pegawai yang cocok dengan pencarian Anda.'
                                    : 'Belum ada pegawai.' }}
                            </p>
                            @if (request()->hasAny(['cari', 'peran']))
                                <a href="{{ route('admin.users.index') }}" class="inline-block mt-2 text-xs text-primary-600 hover:underline">
                                    Tampilkan semua pegawai
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- Tabel untuk layar lebar --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold">NIP</th>
                                <th class="px-4 py-3 text-left font-semibold">Jabatan / Unit Kerja</th>
                                <th class="px-4 py-3 text-left font-semibold">Peran</th>
                                <th class="px-4 py-3 text-left font-semibold">Atasan Langsung</th>
                                <th class="px-4 py-3 text-right font-semibold">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $user->nip_formatted }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $user->jabatan ?? '-' }}
                                        <span class="block text-xs text-gray-400">{{ $user->unit_kerja ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-md text-[11px] bg-gray-100 text-gray-700">{{ $user->role_label }}</span>
                                        @if ($user->isAtasan())
                                            @if ($user->punyaTandaTangan())
                                                <span class="block text-[11px] text-primary-700 mt-1">tanda tangan tersimpan</span>
                                            @else
                                                <span class="block text-[11px] text-amber-600 mt-1">tanda tangan belum diunggah</span>
                                            @endif
                                        @endif
                                        @if ($user->is_plh_kepala_balai)
                                            <span class="block text-[11px] text-amber-800 font-medium mt-1">sedang jadi Plh Kepala Balai</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $user->atasan->name ?? '—' }}
                                        @if (! $user->atasan_id && $user->perluAtasanLangsung())
                                            <span class="block text-[11px] text-amber-600">belum diatur, cuti tidak bisa diajukan</span>
                                        @elseif (! $user->atasan_id)
                                            <span class="block text-[11px] text-gray-400">menyetujui cutinya sendiri</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                               class="px-2.5 py-1 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-100">Ubah</a>
                                            @if ($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                      onsubmit="return confirm('Hapus pegawai {{ $user->name }}? Seluruh riwayat cutinya ikut terhapus.')">
                                                    @csrf @method('DELETE')
                                                    <button class="px-2.5 py-1 rounded-md border border-rose-300 text-xs text-rose-600 hover:bg-rose-50">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <p class="text-sm text-gray-500">
                                            {{ request()->hasAny(['cari', 'peran'])
                                                ? 'Tidak ada pegawai yang cocok dengan pencarian Anda.'
                                                : 'Belum ada pegawai.' }}
                                        </p>
                                        @if (request()->hasAny(['cari', 'peran']))
                                            <a href="{{ route('admin.users.index') }}" class="inline-block mt-2 text-xs text-primary-600 hover:underline">
                                                Tampilkan semua pegawai
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="px-4 sm:px-5 py-3 border-t border-gray-300">{{ $users->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
