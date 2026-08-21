<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pengajuan Cuti</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Diajukan {{ $leaveRequest->created_at->translatedFormat('d F Y, H:i') }} {{ config('instansi.zona_waktu') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if ($leaveRequest->sudahDisetujuiPenuh())
                    <a href="{{ route('leave.cetak', $leaveRequest) }}" target="_blank"
                       class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                        Cetak Surat Cuti
                    </a>
                    <a href="{{ route('leave.cetak', [$leaveRequest, 'unduh' => 1]) }}"
                       class="border border-gray-300 px-4 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                        Unduh PDF
                    </a>
                @else
                    <span class="text-xs text-gray-500 px-2 py-2">
                        Surat dapat dicetak setelah disetujui penuh
                    </span>
                @endif

                @if ($leaveRequest->bolehDiubah() && $leaveRequest->user_id === auth()->id())
                    <a href="{{ route('leave.edit', $leaveRequest) }}"
                       class="border border-primary-400 text-primary-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-50">
                        Ubah Pengajuan
                    </a>
                @endif
                <a href="{{ route('leave.index') }}" class="text-sm text-gray-500 hover:text-gray-800 px-2">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-300 flex items-center justify-between">
                        <h3 class="font-semibold text-sm text-gray-800">{{ $leaveRequest->leaveType->nama_cuti }}</h3>
                        <x-status-badge :status="$leaveRequest->status" />
                    </div>

                    <dl class="divide-y divide-gray-300 text-sm">
                        <div class="px-5 py-3 grid grid-cols-3 gap-4">
                            <dt class="text-gray-500">Tanggal Cuti</dt>
                            <dd class="col-span-2 text-gray-800">
                                {{ $leaveRequest->tanggal_mulai->translatedFormat('d F Y') }} &ndash;
                                {{ $leaveRequest->tanggal_selesai->translatedFormat('d F Y') }}
                            </dd>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-3 gap-4">
                            <dt class="text-gray-500">Lama Cuti</dt>
                            <dd class="col-span-2 text-gray-800 font-medium">{{ $leaveRequest->jumlah_hari }} hari kerja</dd>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-3 gap-4">
                            <dt class="text-gray-500">Alasan</dt>
                            <dd class="col-span-2 text-gray-800">{{ $leaveRequest->alasan }}</dd>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-3 gap-4">
                            <dt class="text-gray-500">Alamat Selama Cuti</dt>
                            <dd class="col-span-2 text-gray-800">
                                {{ $leaveRequest->alamat_cuti ?? '-' }}
                                <span class="block text-xs text-gray-500">Telp: {{ $leaveRequest->telepon_cuti ?? '-' }}</span>
                            </dd>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-3 gap-4">
                            <dt class="text-gray-500">Dokumen Pendukung</dt>
                            <dd class="col-span-2">
                                @if ($leaveRequest->lampiran)
                                    <a href="{{ asset('storage/' . $leaveRequest->lampiran) }}" target="_blank"
                                       class="text-primary-600 hover:underline">Lihat / unduh berkas</a>
                                @else
                                    <span class="text-gray-400">Tidak ada lampiran</span>
                                @endif
                            </dd>
                        </div>
                        @if ($leaveRequest->nomor_surat)
                            <div class="px-5 py-3 grid grid-cols-3 gap-4">
                                <dt class="text-gray-500">Nomor Surat</dt>
                                <dd class="col-span-2 text-gray-800 font-mono text-xs">{{ $leaveRequest->nomor_surat }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Syarat dokumen jenis cuti ini --}}
                @if ($leaveRequest->leaveType->syaratList())
                    <div class="bg-white border border-gray-300 rounded-xl p-5">
                        <h3 class="font-semibold text-sm text-gray-800 mb-3">
                            Syarat Dokumen &mdash; {{ $leaveRequest->leaveType->nama_cuti }}
                        </h3>
                        <ul class="space-y-1.5">
                            @foreach ($leaveRequest->leaveType->syaratList() as $syarat)
                                <li class="flex gap-2 text-xs text-gray-600">
                                    <span class="text-primary-600">&#9679;</span><span>{{ $syarat }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($leaveRequest->leaveType->dasar_hukum)
                            <p class="mt-3 pt-3 border-t border-gray-100 text-[11px] text-gray-400">
                                Dasar hukum: {{ $leaveRequest->leaveType->dasar_hukum }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Linimasa persetujuan --}}
            <div class="bg-white border border-gray-300 rounded-xl p-5 h-fit">
                <h3 class="font-semibold text-sm text-gray-800 mb-4">Jejak Persetujuan</h3>

                @php
                    $langkah = [
                        ['judul' => 'Diajukan', 'nama' => $leaveRequest->user->name, 'waktu' => $leaveRequest->created_at, 'status' => 'selesai', 'catatan' => null],
                    ];

                    $ap1 = $leaveRequest->approvalAtasanLangsung();
                    $langkah[] = [
                        'judul'  => 'Atasan Langsung',
                        'nama'   => $ap1?->approver->name ?? ($leaveRequest->user->atasan->name ?? 'Belum ditentukan'),
                        'waktu'  => $ap1?->tanggal_keputusan,
                        'status' => $ap1 ? ($ap1->keputusan === 'disetujui' ? 'selesai' : 'ditolak') : ($leaveRequest->status === 'menunggu' ? 'proses' : 'nanti'),
                        'catatan' => $ap1?->catatan,
                    ];

                    $ap2 = $leaveRequest->approvalKepalaBalai();
                    $langkah[] = [
                        'judul'  => 'Pejabat Pemberi Cuti',
                        'nama'   => $ap2?->approver->name ?? 'Kepala Balai',
                        'waktu'  => $ap2?->tanggal_keputusan,
                        'status' => $ap2 ? ($ap2->keputusan === 'disetujui' ? 'selesai' : 'ditolak') : ($leaveRequest->status === 'disetujui_atasan' ? 'proses' : 'nanti'),
                        'catatan' => $ap2?->catatan,
                    ];
                @endphp

                <ol class="space-y-4">
                    @foreach ($langkah as $l)
                        @php
                            $warna = match ($l['status']) {
                                'selesai' => 'bg-emerald-500 text-white',
                                'ditolak' => 'bg-rose-500 text-white',
                                'proses'  => 'bg-amber-400 text-white animate-pulse',
                                default   => 'bg-gray-200 text-gray-500',
                            };
                            $ikon = match ($l['status']) {
                                'selesai' => '✓',
                                'ditolak' => '✕',
                                'proses'  => '⋯',
                                default   => '·',
                            };
                        @endphp
                        <li class="flex gap-3">
                            <span class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs {{ $warna }}">{{ $ikon }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $l['judul'] }}</p>
                                <p class="text-xs text-gray-500">{{ $l['nama'] }}</p>
                                @if ($l['waktu'])
                                    <p class="text-[11px] text-gray-400">{{ $l['waktu']->translatedFormat('d M Y, H:i') }}</p>
                                @endif
                                @if ($l['catatan'])
                                    <p class="mt-1 text-[11px] text-gray-600 bg-gray-50 rounded p-2">{{ $l['catatan'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                @if ($leaveRequest->bolehDiubah() && $leaveRequest->user_id === auth()->id())
                    <div class="mt-5 pt-4 border-t border-gray-100 space-y-2">
                        <a href="{{ route('leave.edit', $leaveRequest) }}"
                           class="block text-center w-full py-2 rounded-lg border border-primary-300 text-sm text-primary-700 hover:bg-primary-50">
                            Ubah Pengajuan
                        </a>

                        <form method="POST" action="{{ route('leave.destroy', $leaveRequest) }}"
                              onsubmit="return confirm('Batalkan pengajuan cuti ini? Pengajuan akan dihapus dan tidak bisa dikembalikan.')">
                            @csrf @method('DELETE')
                            <button class="w-full py-2 rounded-lg border border-rose-300 text-sm text-rose-600 hover:bg-rose-50">
                                Batalkan Pengajuan
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
