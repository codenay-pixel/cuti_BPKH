<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Persetujuan Cuti</h2>
        <p class="text-sm text-gray-500 mt-0.5">Pertimbangan Atasan Langsung atas pengajuan cuti bawahan Anda</p>
    </x-slot>

    <div class="pb-12" x-data="{ tolakId: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ==================== MENUNGGU KEPUTUSAN ==================== --}}
            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-gray-300 flex items-center justify-between gap-2">
                    <h3 class="font-semibold text-sm text-gray-800">Menunggu Keputusan Anda</h3>
                    <span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-medium {{ $pengajuan->count() ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500' }}">
                        {{ $pengajuan->count() }} pengajuan
                    </span>
                </div>

                {{-- Kartu untuk HP --}}
                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($pengajuan as $item)
                        <div class="p-4 space-y-3">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $item->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->user->jabatan ?? '-' }}</p>
                                <p class="text-[11px] text-gray-400 font-mono">{{ $item->user->nip_formatted }}</p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-3 space-y-1.5">
                                <p class="text-sm font-medium text-primary-700">{{ $item->leaveType->nama_cuti }}</p>
                                <p class="text-sm text-gray-700">
                                    {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                                    <span class="text-gray-300">&rarr;</span>
                                    {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                                    <span class="ms-1 px-1.5 py-0.5 rounded bg-white border border-gray-300 text-xs">{{ $item->jumlah_hari }} hari</span>
                                </p>
                                <p class="text-xs text-gray-600">{{ $item->alasan }}</p>
                                @if ($item->alamat_cuti)
                                    <p class="text-[11px] text-gray-500">
                                        Alamat: {{ $item->alamat_cuti }} &middot; {{ $item->telepon_cuti }}
                                    </p>
                                @endif
                                @if ($item->lampiran)
                                    <a href="{{ $item->lampiran_url }}" target="_blank"
                                       class="inline-block text-xs text-primary-600 hover:underline">Lihat dokumen pendukung</a>
                                @else
                                    <p class="text-[11px] text-gray-400">Tanpa lampiran</p>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('approval.approve', $item) }}" class="flex-1"
                                      onsubmit="return confirm('Setujui pengajuan cuti {{ $item->user->name }}?')">
                                    @csrf
                                    <button class="w-full px-3 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-medium">Setujui</button>
                                </form>
                                <button type="button" @click="tolakId = (tolakId === {{ $item->id }} ? null : {{ $item->id }})"
                                        class="flex-1 px-3 py-2.5 rounded-lg border border-rose-300 text-rose-600 text-sm font-medium">
                                    Tolak
                                </button>
                            </div>

                            <div x-show="tolakId === {{ $item->id }}" x-cloak style="display:none">
                                <form method="POST" action="{{ route('approval.reject', $item) }}" class="space-y-2">
                                    @csrf
                                    <textarea name="catatan" rows="3" required maxlength="500"
                                              placeholder="Alasan penolakan (wajib diisi)"
                                              class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                                    <div class="flex gap-2">
                                        <button type="button" @click="tolakId = null"
                                                class="flex-1 px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-600">Batal</button>
                                        <button class="flex-1 px-3 py-2 rounded-lg bg-rose-600 text-white text-sm font-medium">Kirim Penolakan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-12 text-center text-sm text-gray-500">
                            Tidak ada pengajuan yang menunggu keputusan Anda.
                        </p>
                    @endforelse
                </div>

                {{-- Tabel untuk layar lebar --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 text-left font-semibold">Pegawai</th>
                                <th class="px-4 py-3 text-left font-semibold">Jenis Cuti</th>
                                <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                                <th class="px-4 py-3 text-center font-semibold">Lama</th>
                                <th class="px-4 py-3 text-center font-semibold">Berkas</th>
                                <th class="px-4 py-3 text-right font-semibold">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($pengajuan as $item)
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $item->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->user->jabatan ?? '-' }}</p>
                                        <p class="text-[11px] text-gray-400 font-mono">{{ $item->user->nip_formatted }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $item->leaveType->nama_cuti }}</p>
                                        <p class="text-xs text-gray-500 max-w-xs">{{ $item->alasan }}</p>
                                        @if ($item->alamat_cuti)
                                            <p class="text-[11px] text-gray-400 mt-1">
                                                Alamat: {{ $item->alamat_cuti }} &middot; {{ $item->telepon_cuti }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                        {{ $item->tanggal_mulai->translatedFormat('d M Y') }}<br>
                                        <span class="text-xs text-gray-500">s/d {{ $item->tanggal_selesai->translatedFormat('d M Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">{{ $item->jumlah_hari }} hari</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($item->lampiran)
                                            <a href="{{ $item->lampiran_url }}" target="_blank"
                                               class="text-primary-600 hover:underline text-xs">Lihat</a>
                                        @else
                                            <span class="text-gray-300 text-xs">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                            <form method="POST" action="{{ route('approval.approve', $item) }}"
                                                  onsubmit="return confirm('Setujui pengajuan cuti {{ $item->user->name }}?')">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs hover:bg-emerald-700">Setujui</button>
                                            </form>
                                            <button type="button" @click="tolakId = (tolakId === {{ $item->id }} ? null : {{ $item->id }})"
                                                    class="px-3 py-1.5 rounded-md border border-rose-300 text-rose-600 text-xs hover:bg-rose-50">Tolak</button>
                                        </div>

                                        <div x-show="tolakId === {{ $item->id }}" x-cloak class="mt-3" style="display:none">
                                            <form method="POST" action="{{ route('approval.reject', $item) }}" class="space-y-2">
                                                @csrf
                                                <textarea name="catatan" rows="2" required maxlength="500"
                                                          placeholder="Alasan penolakan (wajib diisi)"
                                                          class="w-full rounded-lg border-gray-300 text-xs"></textarea>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="tolakId = null" class="text-xs text-gray-500">Batal</button>
                                                    <button class="px-3 py-1.5 rounded-md bg-rose-600 text-white text-xs hover:bg-rose-700">Kirim Penolakan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                        Tidak ada pengajuan yang menunggu keputusan Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ==================== RIWAYAT KEPUTUSAN ==================== --}}
            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-gray-300">
                    <h3 class="font-semibold text-sm text-gray-800">Riwayat Keputusan Anda</h3>
                    <p class="text-xs text-gray-500">20 keputusan terakhir</p>
                </div>

                {{-- Kartu untuk HP --}}
                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($riwayat as $item)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $item->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->leaveType->nama_cuti }}</p>
                                </div>
                                <x-status-badge :status="$item->status" />
                            </div>

                            <p class="text-sm text-gray-700">
                                {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                                <span class="text-gray-300">&rarr;</span>
                                {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                            </p>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Atasan Langsung</p>
                                    <x-approval-badge :approval="$item->approvalAtasanLangsung()" :menunggu="$item->status === 'menunggu'" />
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Pejabat Pemberi Cuti</p>
                                    <x-approval-badge :approval="$item->approvalKepalaBalai()" :menunggu="$item->status === 'disetujui_atasan'" />
                                </div>
                            </div>

                            @if ($item->sudahDisetujuiPenuh())
                                <a href="{{ route('leave.cetak', $item) }}" target="_blank"
                                   class="block text-center px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-700">
                                    Cetak Surat
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="px-4 py-10 text-center text-sm text-gray-500">Belum ada riwayat keputusan.</p>
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
                                <th class="px-4 py-3 text-center font-semibold">Persetujuan Atasan</th>
                                <th class="px-4 py-3 text-center font-semibold">Persetujuan Pejabat<br>Pemberi Cuti</th>
                                <th class="px-4 py-3 text-right font-semibold">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($riwayat as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->user->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->leaveType->nama_cuti }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_mulai->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $item->tanggal_selesai->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-approval-badge :approval="$item->approvalAtasanLangsung()" :menunggu="$item->status === 'menunggu'" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <x-approval-badge :approval="$item->approvalKepalaBalai()" :menunggu="$item->status === 'disetujui_atasan'" />
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($item->sudahDisetujuiPenuh())
                                            <a href="{{ route('leave.cetak', $item) }}" target="_blank"
                                               class="px-2.5 py-1 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-100">Cetak</a>
                                        @else
                                            <span class="text-gray-300 text-xs">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada riwayat keputusan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
