<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                Riwayat Kegiatan
                @unless ($tahunIniBerjalan)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">
                        Arsip {{ $tahun }}
                    </span>
                @endunless
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">Rekap kegiatan pegawai -- Dinas Luar, Rapat, Diklat, dan lainnya</p>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                <form method="GET" class="grid grid-cols-2 sm:flex sm:flex-wrap sm:items-end gap-3 px-4 sm:px-5 py-4 border-b border-gray-300 bg-gray-50">
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Tahun</label>
                        <select name="tahun" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5 pe-8">
                            @foreach ($tahunTersedia as $th)
                                <option value="{{ $th }}" @selected($tahun === $th)>
                                    {{ $th === now()->year ? $th . ' (berjalan)' : 'Arsip ' . $th }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Bulan</label>
                        <select name="bulan" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5 pe-8">
                            <option value="">Semua bulan</option>
                            @foreach (\Carbon\Carbon::createFromDate(2000, 1, 1)->monthsUntil('2000-12-31') as $b)
                                <option value="{{ $b->month }}" @selected($bulan === $b->month)>{{ $b->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Jenis Kegiatan</label>
                        <select name="jenis" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5 pe-8">
                            <option value="">Semua jenis</option>
                            @foreach ($jenisOptions as $nilai => $label)
                                <option value="{{ $nilai }}" @selected($jenis === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Nama Pegawai</label>
                        <input type="text" name="nama" value="{{ request('nama') }}" placeholder="Cari nama..."
                               class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5">
                    </div>
                    <button class="px-4 py-1.5 rounded-lg bg-gray-800 text-white text-sm hover:bg-gray-700">Tampilkan</button>
                    @if (request()->hasAny(['bulan', 'jenis', 'nama']) || ! $tahunIniBerjalan)
                        <a href="{{ route('admin.dinas-luar.index') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800 text-center">Reset</a>
                    @endif
                    <div class="col-span-2 sm:ms-auto text-xs text-gray-500 sm:self-center">
                        Total {{ $riwayat->total() }} pegawai &middot; {{ $totalKegiatan }} kegiatan
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-gray-300 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800">Detail Kegiatan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Diringkas per pegawai -- klik nama untuk buka semua kegiatannya</p>
                </div>

                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($riwayat as $grup)
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = ! open"
                                    class="w-full p-4 flex items-center justify-between gap-3 text-left">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $grup['user']->name ?? 'Pegawai tidak diketahui' }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $grup['user']->nip_formatted ?? '—' }}</p>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <span class="px-2 py-1 rounded-md text-[11px] bg-accent-500/15 text-accent-700 font-medium">
                                        {{ $grup['jumlah_kegiatan'] }} kegiatan
                                    </span>
                                    <x-ikon nama="panah-bawah" kelas="w-4 h-4 text-gray-400 transition-transform duration-150 shrink-0"
                                            x-bind:class="{ 'rotate-180': open }" />
                                </div>
                            </button>

                            <div x-show="open" x-cloak class="divide-y divide-gray-200 bg-gray-50">
                                @foreach ($grup['kegiatan'] as $item)
                                    <div class="p-4 pl-6 space-y-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="text-sm font-medium text-gray-700">{{ $item->jenis_label }}</p>
                                            <div class="shrink-0 flex items-center gap-2">
                                                <span class="px-2 py-1 rounded-md text-[11px] bg-accent-500/15 text-accent-700 font-medium">
                                                    {{ $item->lama_hari }} hari
                                                </span>
                                                <form method="POST" action="{{ route('events.destroy', $item) }}"
                                                      onsubmit="return confirm('Hapus kegiatan ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="px-2.5 py-1 rounded-md text-[11px] font-semibold text-rose-600 border border-rose-200 hover:bg-rose-50">
                                                        HAPUS
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700">
                                            {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                                            <span class="text-gray-300">&rarr;</span>
                                            {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                                        </p>
                                        @if ($item->nomor_spt)
                                            <p class="text-xs text-gray-500">No. Surat {{ $item->nomor_spt }}</p>
                                        @endif
                                        @if ($item->dicatat_oleh_id && $item->dicatat_oleh_id !== $item->user_id)
                                            <p class="text-[11px] text-gray-400">Dicatat oleh {{ $item->dicatatOleh?->name }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada data{{ $tahunIniBerjalan ? '' : ' di arsip ' . $tahun }}.</p>
                    @endforelse
                </div>

                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 text-left font-semibold">Pegawai</th>
                                <th class="px-4 py-3 text-left font-semibold">Jenis</th>
                                <th class="px-4 py-3 text-left font-semibold">Nomor Surat</th>
                                <th class="px-4 py-3 text-left font-semibold">Tanggal Mulai</th>
                                <th class="px-4 py-3 text-left font-semibold">Tanggal Selesai</th>
                                <th class="px-4 py-3 text-center font-semibold">Lama</th>
                                <th class="px-4 py-3 text-left font-semibold">Dicatat Oleh</th>
                                <th class="px-4 py-3 text-center font-semibold">Hapus</th>
                            </tr>
                        </thead>
                        @forelse ($riwayat as $grup)
                            <tbody x-data="{ open: false }" class="divide-y divide-gray-300 border-t border-gray-300">
                                <tr class="hover:bg-gray-50 cursor-pointer" @click="open = ! open">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <x-ikon nama="panah-bawah" kelas="w-3.5 h-3.5 text-gray-400 transition-transform duration-150 shrink-0"
                                                    x-bind:class="{ 'rotate-180': open }" />
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-800">{{ $grup['user']->name ?? 'Pegawai tidak diketahui' }}</p>
                                                <p class="text-[11px] text-gray-400 font-mono">{{ $grup['user']->nip_formatted ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500" colspan="4">
                                        {{ $grup['jumlah_kegiatan'] }} kegiatan
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-gray-700 whitespace-nowrap">
                                        {{ $grup['total_hari'] }} hari
                                    </td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                @foreach ($grup['kegiatan'] as $item)
                                    <tr x-show="open" x-cloak class="bg-gray-50">
                                        <td class="px-4 py-2"></td>
                                        <td class="px-4 py-2 text-gray-700">{{ $item->jenis_label }}</td>
                                        <td class="px-4 py-2 text-gray-700">{{ $item->nomor_spt ?? '—' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ $item->tanggal_selesai->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap">{{ $item->lama_hari }} hari</td>
                                        <td class="px-4 py-2 text-gray-700">{{ $item->dicatatOleh?->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <form method="POST" action="{{ route('events.destroy', $item) }}"
                                                  onsubmit="return confirm('Hapus kegiatan ini?')">
                                                @csrf @method('DELETE')
                                                <button class="px-2.5 py-1 rounded-md text-[11px] font-semibold text-rose-600 border border-rose-200 hover:bg-rose-50">
                                                    HAPUS
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @empty
                            <tbody>
                                <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada data{{ $tahunIniBerjalan ? '' : ' di arsip ' . $tahun }}.</td></tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>

                @if ($riwayat->hasPages())
                    <div class="px-4 sm:px-5 py-3 border-t border-gray-300">{{ $riwayat->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
