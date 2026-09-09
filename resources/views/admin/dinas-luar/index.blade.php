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
                    <div class="col-span-2 sm:ms-auto text-xs text-gray-500 sm:self-center">Total {{ $riwayat->total() }} kegiatan</div>
                </form>

                <div class="px-4 sm:px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">
                        Rekap per Pegawai
                        @if ($bulan)
                            &mdash; {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
                        @else
                            &mdash; Tahun {{ $tahun }}
                        @endif
                    </h3>

                    @if ($rekap->isEmpty())
                        <p class="text-sm text-gray-500 py-4 text-center">Belum ada data kegiatan pada periode ini.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($rekap as $r)
                                <div class="rounded-xl border border-gray-300 bg-gray-50 px-4 py-3">
                                    <p class="font-medium text-gray-800 text-sm truncate">{{ $r['user']->name ?? 'Pegawai tidak diketahui' }}</p>
                                    <p class="text-[11px] text-gray-500 mb-2">{{ $r['user']->jabatan ?? '—' }}</p>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-1 rounded-md bg-accent-500/15 text-accent-700 text-xs font-semibold">
                                            {{ $r['total_hari'] }} hari
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $r['jumlah_kegiatan'] }} kegiatan</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-gray-300 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800">Detail Kegiatan</h3>
                </div>

                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($riwayat as $item)
                        <div class="p-4 space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $item->user->name }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $item->user->nip_formatted }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-md text-[11px] bg-accent-500/15 text-accent-700 font-medium">
                                    {{ $item->lama_hari }} hari
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $item->jenis_label }}</p>
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($riwayat as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $item->user->name }}</p>
                                        <p class="text-[11px] text-gray-400 font-mono">{{ $item->user->nip_formatted }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->jenis_label }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->nomor_spt ?? '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_selesai->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">{{ $item->lama_hari }} hari</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->dicatatOleh?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada data{{ $tahunIniBerjalan ? '' : ' di arsip ' . $tahun }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($riwayat->hasPages())
                    <div class="px-4 sm:px-5 py-3 border-t border-gray-300">{{ $riwayat->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
