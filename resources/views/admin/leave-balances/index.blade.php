@php
    $thn = fn ($i) => $tahun - $i;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Saldo Cuti Tahunan</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            Hak 12 hari per tahun. Sisa {{ $thn(1) }} dan {{ $thn(2) }} masing-masing hanya boleh
            dipakai maksimal 6 hari pada tahun {{ $tahun }}.
        </p>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">

                {{-- Penyaring --}}
                <form method="GET" class="grid grid-cols-2 sm:flex sm:flex-wrap sm:items-end gap-3 px-4 sm:px-5 py-4 border-b border-gray-300 bg-gray-50">
                    <div class="col-span-2 sm:col-auto">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Cari Pegawai</label>
                        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Nama atau NIP..."
                               class="w-full sm:w-56 rounded-lg border-gray-300 text-sm py-1.5">
                    </div>
                    <button class="px-4 py-1.5 rounded-lg bg-gray-800 text-white text-sm hover:bg-gray-700">Cari</button>
                    @if (request()->filled('cari'))
                        <a href="{{ route('admin.leave-balances.index') }}"
                           class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800 text-center">Reset</a>
                    @endif
                    <div class="col-span-2 sm:ms-auto text-xs text-gray-500 sm:self-center">
                        {{ $users->total() }} pegawai &middot; tahun berjalan {{ $tahun }}
                    </div>
                </form>

                {{-- ===================== KARTU (HP) ===================== --}}
                <div class="lg:hidden divide-y divide-gray-300">
                    @forelse ($baris as $b)
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $b['user']->name }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $b['user']->nip_formatted }}</p>
                                </div>
                                <a href="{{ route('admin.leave-balances.edit', $b['user']) }}"
                                   class="shrink-0 px-2.5 py-1 rounded-md border border-gray-300 text-xs text-gray-700">Atur</a>
                            </div>

                            <div class="mt-3 grid grid-cols-4 divide-x divide-gray-400 border border-gray-400 rounded-lg overflow-hidden text-center">
                                @foreach ($b['tahun'] as $t => $d)
                                    <div class="py-2 {{ $d['berjalan'] ? 'bg-primary-50' : '' }}">
                                        <p class="text-[10px] text-gray-400">{{ $t }}</p>
                                        <p class="text-lg font-bold {{ $d['tersedia'] > 0 ? 'text-primary-700' : 'text-gray-300' }}">
                                            {{ $d['tersedia'] }}
                                        </p>
                                        @if ($d['dibatasi'])
                                            <p class="text-[9px] text-amber-600">dari {{ $d['sisa'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                                <div class="py-2 bg-gray-800 text-white">
                                    <p class="text-[10px] text-gray-300">Total</p>
                                    <p class="text-lg font-bold">{{ $b['total'] }}</p>
                                </div>
                            </div>

                            @unless ($b['ada_data'])
                                <p class="mt-2 text-[11px] text-amber-600">Belum ada data saldo — klik Atur untuk mengisi.</p>
                            @endunless
                        </div>
                    @empty
                        <p class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada pegawai yang cocok.</p>
                    @endforelse
                </div>

                {{-- ===================== TABEL (layar lebar) ===================== --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wide">
                                <th rowspan="2" class="px-4 py-2 text-left font-semibold align-bottom">Pegawai</th>
                                <th colspan="3" class="px-4 py-2 text-center font-semibold border-b border-gray-300">
                                    Hari yang Dapat Diambil
                                </th>
                                <th rowspan="2" class="px-4 py-2 text-center font-semibold align-bottom">Total</th>
                                <th rowspan="2" class="px-4 py-2 text-center font-semibold align-bottom">Terpakai<br>{{ $tahun }}</th>
                                <th rowspan="2" class="px-4 py-2 text-right font-semibold align-bottom">Tindakan</th>
                            </tr>
                            <tr class="bg-gray-200 text-gray-700 text-xs">
                                <th class="px-4 py-2 text-center font-medium">
                                    Sisa {{ $thn(2) }}<span class="block text-[10px] font-normal text-gray-400">maks 6</span>
                                </th>
                                <th class="px-4 py-2 text-center font-medium">
                                    Sisa {{ $thn(1) }}<span class="block text-[10px] font-normal text-gray-400">maks 6</span>
                                </th>
                                <th class="px-4 py-2 text-center font-medium">
                                    Hak {{ $tahun }}<span class="block text-[10px] font-normal text-gray-400">tahun berjalan</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse ($baris as $b)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $b['user']->name }}</p>
                                        <p class="text-[11px] text-gray-400 font-mono">{{ $b['user']->nip_formatted }}</p>
                                        @unless ($b['ada_data'])
                                            <p class="text-[11px] text-amber-600">belum ada data saldo</p>
                                        @endunless
                                    </td>

                                    @foreach ($b['tahun'] as $t => $d)
                                        <td class="px-4 py-3 text-center {{ $d['berjalan'] ? 'bg-primary-50/60' : '' }}">
                                            <span class="text-lg font-semibold {{ $d['tersedia'] > 0 ? 'text-primary-700' : 'text-gray-300' }}">
                                                {{ $d['tersedia'] }}
                                            </span>
                                            @if ($d['dibatasi'])
                                                <span class="block text-[10px] text-amber-600" title="Sisa {{ $d['sisa'] }} hari, tapi hanya 6 yang boleh dipakai">
                                                    dari {{ $d['sisa'] }} sisa
                                                </span>
                                            @elseif ($d['berjalan'])
                                                <span class="block text-[10px] text-gray-400">jatah {{ $d['jatah'] }}</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2 py-1 rounded-md
                                                     {{ $b['total'] > 0 ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-400' }} font-semibold">
                                            {{ $b['total'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center text-gray-600">
                                        {{ $b['tahun'][$tahun]['terpakai'] }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.leave-balances.edit', $b['user']) }}"
                                           class="px-2.5 py-1 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-100">Atur</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada pegawai yang cocok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="px-4 sm:px-5 py-3 border-t border-gray-300">{{ $users->links() }}</div>
                @endif
            </div>

            <p class="mt-4 text-xs text-gray-400 text-center">
                Angka besar adalah hari yang benar-benar boleh diambil tahun {{ $tahun }}.
                Sisa {{ $thn(2) }} dan {{ $thn(1) }} yang lebih dari 6 hari tetap tercatat, tetapi hanya 6 yang terhitung.
            </p>
        </div>
    </div>
</x-app-layout>
