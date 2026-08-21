<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengajuan Cuti</h2>
                <p class="text-sm text-gray-500 mt-0.5">Riwayat dan status persetujuan cuti Anda</p>
            </div>
            <a href="{{ route('leave.create') }}"
               class="inline-flex items-center justify-center gap-1.5 bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition w-full sm:w-auto">
                <span class="text-lg leading-none">+</span> Ajukan Cuti
            </a>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-saldo-cuti :saldo="$saldo" />

            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">

                {{-- Filter --}}
                <form method="GET" class="grid grid-cols-2 sm:flex sm:flex-wrap sm:items-end gap-3 px-4 sm:px-5 py-4 border-b border-gray-300 bg-gray-50">
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Jenis Cuti</label>
                        <select name="jenis" class="w-full sm:w-auto rounded-lg border-gray-300 text-sm py-1.5 pe-8">
                            <option value="">Semua jenis</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('jenis') == $type->id)>{{ $type->nama_cuti }}</option>
                            @endforeach
                        </select>
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

                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-gray-800 text-white text-sm hover:bg-gray-700">
                        Tampilkan
                    </button>

                    @if (request()->hasAny(['jenis', 'status']))
                        <a href="{{ route('leave.index') }}"
                           class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800 text-center">Reset</a>
                    @endif

                    <div class="col-span-2 sm:ms-auto text-xs text-gray-500 sm:self-center">
                        Total {{ $riwayat->total() }} pengajuan
                    </div>
                </form>

                {{-- ========== TAMPILAN KARTU (HP & tablet kecil) ========== --}}
                <div class="xl:hidden divide-y divide-gray-300">
                    @forelse ($riwayat as $item)
                        @php
                            $apAtasan = $item->approvalAtasanLangsung();
                            $apPejabat = $item->approvalKepalaBalai();
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('leave.show', $item) }}" class="font-semibold text-primary-700 hover:underline">
                                        {{ $item->leaveType->nama_cuti }}
                                    </a>
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $item->alasan }}</p>
                                </div>
                                <x-status-badge :status="$item->status" />
                            </div>

                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <span>{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</span>
                                <span class="text-gray-300">&rarr;</span>
                                <span>{{ $item->tanggal_selesai->translatedFormat('d M Y') }}</span>
                                <span class="ms-auto shrink-0 px-2 py-0.5 rounded bg-gray-100 text-xs font-medium">{{ $item->jumlah_hari }} hari</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Atasan Langsung</p>
                                    <x-approval-badge :approval="$apAtasan" :menunggu="$item->status === 'menunggu'" />
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Pejabat Pemberi Cuti</p>
                                    <x-approval-badge :approval="$apPejabat" :menunggu="$item->status === 'disetujui_atasan'" />
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <a href="{{ route('leave.show', $item) }}"
                                   class="flex-1 min-w-[5rem] text-center px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-700">
                                    Lihat
                                </a>

                                @if ($item->bolehDiubah())
                                    <a href="{{ route('leave.edit', $item) }}"
                                       class="flex-1 min-w-[5rem] text-center px-3 py-2 rounded-lg border border-primary-300 text-xs text-primary-700">
                                        Ubah
                                    </a>
                                @endif

                                @if ($item->lampiran)
                                    <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank"
                                       class="flex-1 min-w-[5rem] text-center px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-700">
                                        Berkas
                                    </a>
                                @endif

                                @if ($item->sudahDisetujuiPenuh())
                                    <a href="{{ route('leave.cetak', $item) }}" target="_blank"
                                       class="flex-1 min-w-[7rem] text-center px-3 py-2 rounded-lg bg-primary-600 text-white text-xs font-medium">
                                        Cetak Surat
                                    </a>
                                @endif

                                @if ($item->bolehDiubah())
                                    <form method="POST" action="{{ route('leave.destroy', $item) }}" class="flex-1 min-w-[6rem]"
                                          onsubmit="return confirm('Batalkan pengajuan cuti ini? Pengajuan akan dihapus dan tidak bisa dikembalikan.')">
                                        @csrf @method('DELETE')
                                        <button class="w-full px-3 py-2 rounded-lg border border-rose-300 text-xs text-rose-600">Batalkan</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center">
                            <p class="text-gray-500 text-sm">Belum ada pengajuan cuti.</p>
                            <a href="{{ route('leave.create') }}" class="inline-block mt-3 text-primary-600 text-sm hover:underline">
                                Ajukan cuti pertama Anda &rarr;
                            </a>
                        </div>
                    @endforelse
                </div>

                {{-- ========== TAMPILAN TABEL (layar lebar) ========== --}}
                <div class="hidden xl:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wide">
                                <th class="px-3 py-3 text-left font-semibold">Jenis Cuti</th>
                                <th class="px-3 py-3 text-left font-semibold">Tanggal Mulai</th>
                                <th class="px-3 py-3 text-left font-semibold">Tanggal Selesai</th>
                                <th class="px-3 py-3 text-center font-semibold">Lama</th>
                                <th class="px-3 py-3 text-center font-semibold">Persetujuan Atasan</th>
                                <th class="px-3 py-3 text-center font-semibold">Persetujuan Pejabat<br>Pemberi Cuti</th>
                                <th class="px-3 py-3 text-center font-semibold">Berkas</th>
                                <th class="px-3 py-3 text-right font-semibold">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($riwayat as $item)
                                @php
                                    $apAtasan = $item->approvalAtasanLangsung();
                                    $apPejabat = $item->approvalKepalaBalai();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3">
                                        <a href="{{ route('leave.show', $item) }}" class="font-medium text-primary-700 hover:underline">
                                            {{ $item->leaveType->nama_cuti }}
                                        </a>
                                        <p class="text-xs text-gray-500 mt-0.5 max-w-[16rem] truncate" title="{{ $item->alasan }}">
                                            {{ $item->alasan }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_selesai->translatedFormat('d M Y') }}</td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap text-gray-700">{{ $item->jumlah_hari }} hari</td>
                                    <td class="px-3 py-3 text-center">
                                        <x-approval-badge :approval="$apAtasan" :menunggu="$item->status === 'menunggu'" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <x-approval-badge :approval="$apPejabat" :menunggu="$item->status === 'disetujui_atasan'" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if ($item->lampiran)
                                            <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank"
                                               class="text-primary-600 hover:underline text-xs">Lihat</a>
                                        @else
                                            <span class="text-gray-300 text-xs">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                            <a href="{{ route('leave.show', $item) }}"
                                               class="px-2.5 py-1 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-100">Lihat</a>

                                            @if ($item->bolehDiubah())
                                                <a href="{{ route('leave.edit', $item) }}"
                                                   class="px-2.5 py-1 rounded-md border border-primary-300 text-xs text-primary-700 hover:bg-primary-50">Ubah</a>
                                            @endif

                                            @if ($item->sudahDisetujuiPenuh())
                                                <a href="{{ route('leave.cetak', $item) }}" target="_blank"
                                                   class="px-2.5 py-1 rounded-md bg-primary-600 text-white text-xs hover:bg-primary-700 whitespace-nowrap">
                                                    Cetak Surat
                                                </a>
                                            @endif

                                            @if ($item->bolehDiubah())
                                                <form method="POST" action="{{ route('leave.destroy', $item) }}"
                                                      onsubmit="return confirm('Batalkan pengajuan cuti ini? Pengajuan akan dihapus dan tidak bisa dikembalikan.')">
                                                    @csrf @method('DELETE')
                                                    <button class="px-2.5 py-1 rounded-md border border-rose-300 text-xs text-rose-600 hover:bg-rose-50">Batalkan</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <p class="text-gray-500 text-sm">Belum ada pengajuan cuti.</p>
                                        <a href="{{ route('leave.create') }}" class="inline-block mt-3 text-primary-600 text-sm hover:underline">
                                            Ajukan cuti pertama Anda &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($riwayat->hasPages())
                    <div class="px-4 sm:px-5 py-3 border-t border-gray-300">{{ $riwayat->links() }}</div>
                @endif
            </div>

            <p class="text-xs text-gray-400 text-center px-4">
                Formulir cuti baru dapat dicetak setelah disetujui oleh atasan langsung dan pejabat pemberi cuti.
            </p>
        </div>
    </div>
</x-app-layout>
