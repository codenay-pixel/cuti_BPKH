<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->jabatan ?? '—' }} &middot; {{ $user->role_label }}</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.users.index') }}"
                   class="flex-1 sm:flex-none text-center px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    &larr; Kembali
                </a>
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="flex-1 sm:flex-none text-center bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                    Ubah Data
                </a>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white border border-gray-300 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Data Kepegawaian</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">NIP</dt>
                        <dd class="text-gray-800 font-mono">{{ $user->nip_formatted }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Peran</dt>
                        <dd class="text-gray-800">{{ $user->role_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Unit Kerja</dt>
                        <dd class="text-gray-800">{{ $user->unit_kerja ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Atasan Langsung</dt>
                        <dd class="text-gray-800">{{ $user->atasan->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">TMT PNS</dt>
                        <dd class="text-gray-800">{{ optional($user->tmt_pns)->translatedFormat('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Masa Kerja</dt>
                        <dd class="text-gray-800">{{ $user->masa_kerja }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Nomor Telepon</dt>
                        <dd class="text-gray-800">{{ $user->no_telp ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Email</dt>
                        <dd class="text-gray-800">{{ $user->email ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-300 bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Riwayat Kegiatan</h3>
                        <p class="text-xs text-gray-500 mt-0.5">10 kegiatan terbaru</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-lg font-semibold text-accent-700 leading-none">{{ $totalHariBulanIni }}</p>
                            <p class="text-[11px] text-gray-500">hari bulan ini</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-gray-800 leading-none">{{ $jumlahBulanIni }}</p>
                            <p class="text-[11px] text-gray-500">kegiatan bulan ini</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-300">
                    @forelse ($riwayatDinasLuar as $acara)
                        <div class="px-5 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ $acara->nama_acara }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $acara->tanggal_mulai->translatedFormat('d M Y') }}
                                        <span class="text-gray-300">&rarr;</span>
                                        {{ $acara->tanggal_selesai->translatedFormat('d M Y') }}
                                    </p>
                                    @if ($acara->nomor_spt)
                                        <p class="text-[11px] text-gray-400">SPT No. {{ $acara->nomor_spt }}</p>
                                    @endif
                                    @if ($acara->dicatat_oleh_id && $acara->dicatat_oleh_id !== $acara->user_id)
                                        <p class="text-[11px] text-gray-400">Dicatat oleh {{ $acara->dicatatOleh?->name }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-md text-[11px] bg-accent-500/15 text-accent-700 font-medium">
                                    {{ $acara->lama_hari }} hari
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-gray-500">Belum ada riwayat Dinas Luar.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
