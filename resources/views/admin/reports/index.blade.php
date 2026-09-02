<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    Rekap Pengajuan Cuti
                    @unless ($tahunIniBerjalan)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">
                            Arsip {{ $tahun }}
                        </span>
                    @endunless
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if ($tahunIniBerjalan)
                        Seluruh pengajuan cuti pegawai tahun {{ $tahun }}
                    @else
                        Data arsip tahun {{ $tahun }} &mdash; tahun berjalan saat ini {{ now()->year }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.reports.export-excel', request()->query()) }}"
                   class="flex-1 sm:flex-none text-center bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">Ekspor Excel</a>
                <a href="{{ route('admin.reports.export-pdf', request()->query()) }}"
                   class="flex-1 sm:flex-none text-center bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-700">Ekspor PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Nama Pegawai</label>
                        <input type="text" name="nama" value="{{ request('nama') }}" placeholder="Cari nama..."
                               class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5">
                    </div>
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5 pe-8">
                            <option value="">Semua status</option>
                            <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu Atasan</option>
                            <option value="disetujui_atasan" @selected(request('status') === 'disetujui_atasan')>Menunggu Pejabat</option>
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                        </select>
                    </div>
                    <button class="px-4 py-1.5 rounded-lg bg-gray-800 text-white text-sm hover:bg-gray-700">Tampilkan</button>
                    @if (request()->hasAny(['nama', 'status']) || ! $tahunIniBerjalan)
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800 text-center">Reset</a>
                    @endif
                    <div class="col-span-2 sm:ms-auto text-xs text-gray-500 sm:self-center">Total {{ $riwayat->total() }} pengajuan</div>
                </form>

                {{-- Kartu untuk HP --}}
                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($riwayat as $item)
                        <div class="p-4 space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $item->user->name }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $item->user->nip_formatted }}</p>
                                </div>
                                <x-status-badge :status="$item->status" />
                            </div>
                            <p class="text-sm text-primary-700 font-medium">{{ $item->leaveType->nama_cuti }}</p>
                            <p class="text-sm text-gray-700">
                                {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                                <span class="text-gray-300">&rarr;</span>
                                {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                                <span class="ms-1 px-1.5 py-0.5 rounded bg-gray-100 text-xs">{{ $item->jumlah_hari }} hari</span>
                            </p>
                            <div class="flex gap-2 pt-1">
                                @if ($item->sudahDisetujuiPenuh())
                                    <a href="{{ route('leave.cetak', $item) }}" target="_blank"
                                       class="flex-1 text-center px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-700">
                                        Cetak Surat
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.reports.destroy', $item) }}" class="flex-1"
                                      onsubmit="return confirm('Hapus pengajuan cuti {{ $item->user->name }} ({{ $item->tanggal_mulai->translatedFormat('d M Y') }})?\n\nJejak persetujuan dan lampirannya ikut terhapus. Saldo cuti yang sudah terpotong akan dikembalikan.')">
                                    @csrf @method('DELETE')
                                    <button class="w-full px-3 py-2 rounded-lg border border-rose-300 text-xs text-rose-600">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada data{{ $tahunIniBerjalan ? '' : ' di arsip ' . $tahun }}.</p>
                    @endforelse
                </div>

                {{-- Tabel untuk layar lebar --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 text-left font-semibold">Pegawai</th>
                                <th class="px-4 py-3 text-left font-semibold">Jenis Cuti</th>
                                <th class="px-4 py-3 text-left font-semibold">Tanggal Mulai</th>
                                <th class="px-4 py-3 text-left font-semibold">Tanggal Selesai</th>
                                <th class="px-4 py-3 text-center font-semibold">Lama</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-right font-semibold">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($riwayat as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $item->user->name }}</p>
                                        <p class="text-[11px] text-gray-400 font-mono">{{ $item->user->nip_formatted }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->leaveType->nama_cuti }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_selesai->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">{{ $item->jumlah_hari }} hari</td>
                                    <td class="px-4 py-3 text-center"><x-status-badge :status="$item->status" /></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                            @if ($item->sudahDisetujuiPenuh())
                                                <a href="{{ route('leave.cetak', $item) }}" target="_blank"
                                                   class="px-2.5 py-1 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-100">Cetak</a>
                                            @endif
                                            <form method="POST" action="{{ route('admin.reports.destroy', $item) }}"
                                                  onsubmit="return confirm('Hapus pengajuan cuti {{ $item->user->name }} ({{ $item->tanggal_mulai->translatedFormat('d M Y') }})?\n\nJejak persetujuan dan lampirannya ikut terhapus. Saldo cuti yang sudah terpotong akan dikembalikan.')">
                                                @csrf @method('DELETE')
                                                <button class="px-2.5 py-1 rounded-md border border-rose-300 text-xs text-rose-600 hover:bg-rose-50">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
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

            <p class="mt-4 text-xs text-gray-400 text-center">
                Menghapus pengajuan yang sudah disetujui akan mengembalikan saldo cuti tahunan pegawai secara otomatis.
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
    </div>
</x-app-layout>
