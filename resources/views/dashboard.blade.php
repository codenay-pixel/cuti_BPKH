<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Beranda</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ auth()->user()->name }} &middot; {{ auth()->user()->jabatan ?? auth()->user()->role_label }}
        </p>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Saldo cuti tahunan --}}
            <x-saldo-cuti :saldo="$saldo" />

            {{-- Aksi cepat --}}
            @php
                $u = auth()->user();

                $aksi = [
                    ['ikon' => 'cuti-baru', 'warna' => 'bg-primary-600',
                     'judul' => 'Ajukan Cuti', 'ket' => 'Isi formulir permintaan cuti',
                     'tautan' => route('leave.create')],

                    ['ikon' => 'kalender', 'warna' => 'bg-accent-500',
                     'judul' => 'Kalender Kantor', 'ket' => 'Lihat siapa yang cuti atau dinas luar',
                     'tautan' => route('calendar.index')],
                ];

                if ($u->isAtasanLangsung() || $u->isKepalaBalai()) {
                    $aksi[] = [
                        'ikon' => 'persetujuan', 'warna' => 'bg-emerald-600',
                        'judul' => 'Persetujuan',
                        'ket' => $menungguSaya > 0
                            ? $menungguSaya . ' pengajuan menunggu Anda'
                            : 'Tidak ada yang menunggu',
                        'tautan' => $u->isKepalaBalai()
                            ? route('kepala-balai.approval.index')
                            : route('approval.index'),
                        'lencana' => $menungguSaya,
                    ];
                } elseif ($u->isAdmin()) {
                    $aksi[] = ['ikon' => 'rekap', 'warna' => 'bg-sky-700',
                               'judul' => 'Rekap Cuti', 'ket' => 'Ekspor Excel & PDF',
                               'tautan' => route('admin.reports.index')];
                } else {
                    $aksi[] = ['ikon' => 'riwayat', 'warna' => 'bg-sky-700',
                               'judul' => 'Riwayat Cuti Saya', 'ket' => 'Lihat status & cetak formulir',
                               'tautan' => route('leave.index')];
                }

                if ($u->isAdmin()) {
                    $aksi[] = ['ikon' => 'pegawai', 'warna' => 'bg-slate-700',
                               'judul' => 'Kelola Pegawai', 'ket' => 'Akun, peran, dan atasan langsung',
                               'tautan' => route('admin.users.index')];
                    $aksi[] = ['ikon' => 'saldo', 'warna' => 'bg-teal-700',
                               'judul' => 'Saldo Cuti', 'ket' => 'Atur hak dan sisa cuti tahunan',
                               'tautan' => route('admin.leave-balances.index')];
                }
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach ($aksi as $a)
                    <a href="{{ $a['tautan'] }}"
                       class="group relative flex flex-col gap-3 p-4 sm:p-5 bg-white border border-gray-300 rounded-xl
                              hover:border-primary-400 hover:shadow-md transition">

                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg text-white
                                     {{ $a['warna'] }} group-hover:scale-105 transition-transform">
                            <x-ikon :nama="$a['ikon']" kelas="w-6 h-6" />
                        </span>

                        @if (($a['lencana'] ?? 0) > 0)
                            <span class="absolute top-3 right-3 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5
                                         rounded-full bg-rose-500 text-white text-[10px] font-semibold">
                                {{ $a['lencana'] }}
                            </span>
                        @endif

                        <span>
                            <span class="block font-semibold text-gray-800 text-sm leading-tight">{{ $a['judul'] }}</span>
                            <span class="block text-xs text-gray-500 mt-1 leading-snug">{{ $a['ket'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Pengajuan terakhir --}}
                <div class="bg-white border border-gray-300 rounded-xl">
                    <div class="px-5 py-3 border-b border-gray-300 flex items-center justify-between">
                        <h3 class="font-semibold text-sm text-gray-800">Pengajuan Cuti Terakhir</h3>
                        <a href="{{ route('leave.index') }}" class="text-xs text-primary-600 hover:underline">Lihat semua</a>
                    </div>
                    <div class="divide-y divide-gray-300">
                        @forelse ($pengajuanSaya as $item)
                            <a href="{{ route('leave.show', $item) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $item->leaveType->nama_cuti }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->tanggal_mulai->translatedFormat('d M Y') }} &ndash;
                                        {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                                        ({{ $item->jumlah_hari }} hari)
                                    </p>
                                </div>
                                <x-status-badge :status="$item->status" />
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-500 text-center">Belum ada pengajuan cuti.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Siapa yang tidak di kantor hari ini --}}
                <div class="bg-white border border-gray-300 rounded-xl">
                    <div class="px-5 py-3 border-b border-gray-300">
                        <h3 class="font-semibold text-sm text-gray-800">Tidak di Kantor Hari Ini</h3>
                        <p class="text-xs text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="divide-y divide-gray-300">
                        @forelse ($sedangCuti as $cuti)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $cuti->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $cuti->leaveType->nama_cuti }} s/d {{ $cuti->tanggal_selesai->translatedFormat('d M') }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-md text-[11px] bg-primary-100 text-primary-700">Cuti</span>
                            </div>
                        @empty
                        @endforelse

                        @forelse ($sedangDinas as $acara)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $acara->user->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $acara->nama_acara }}{{ $acara->lokasi ? ' — ' . $acara->lokasi : '' }}
                                        s/d {{ $acara->tanggal_selesai->translatedFormat('d M') }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 rounded-md text-[11px] bg-accent-500/20 text-accent-600">Dinas</span>
                            </div>
                        @empty
                        @endforelse

                        @if ($sedangCuti->isEmpty() && $sedangDinas->isEmpty())
                            <p class="px-5 py-6 text-sm text-gray-500 text-center">Semua pegawai berada di kantor hari ini.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
