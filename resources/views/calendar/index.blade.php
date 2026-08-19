<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kalender Cuti Tim
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kalender --}}
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-6">

                    {{-- Navigasi bulan --}}
                    <div class="flex items-center justify-between mb-4">
                        @php
                            $bulanSebelumnya = \Carbon\Carbon::create($tahun, $bulan, 1)->subMonth();
                            $bulanBerikutnya = \Carbon\Carbon::create($tahun, $bulan, 1)->addMonth();
                        @endphp
                        <a href="{{ route('calendar.index', ['bulan' => $bulanSebelumnya->month, 'tahun' => $bulanSebelumnya->year]) }}"
                           class="text-primary-600 hover:underline text-sm">&larr; Bulan Sebelumnya</a>

                        <h3 class="font-semibold text-lg text-gray-800">
                            {{ $awalBulan->translatedFormat('F Y') }}
                        </h3>

                        <a href="{{ route('calendar.index', ['bulan' => $bulanBerikutnya->month, 'tahun' => $bulanBerikutnya->year]) }}"
                           class="text-primary-600 hover:underline text-sm">Bulan Berikutnya &rarr;</a>
                    </div>

                    {{-- Header hari --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 mb-2">
                        <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                    </div>

                    {{-- Grid tanggal --}}
                    <div class="grid grid-cols-7 gap-1">
                        @php
                            $mulaiGrid = $awalBulan->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                            $akhirGrid = $akhirBulan->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                            $hariIni = now()->format('Y-m-d');
                        @endphp

                        @foreach ($mulaiGrid->daysUntil($akhirGrid) as $tanggal)
                            @php
                                $key = $tanggal->format('Y-m-d');
                                $isBulanIni = $tanggal->month === $bulan;
                                $cutiHariItu = $cutiPerTanggal[$key] ?? [];
                            @endphp
                            <div class="border rounded-md min-h-[80px] p-1 text-xs
                                {{ $isBulanIni ? 'bg-white' : 'bg-gray-50 text-gray-400' }}
                                {{ $key === $hariIni ? 'ring-2 ring-primary-500' : '' }}">
                                <div class="font-medium mb-1">{{ $tanggal->day }}</div>
                                @foreach (array_slice($cutiHariItu, 0, 2) as $cuti)
                                    <div class="bg-primary-100 text-primary-700 rounded px-1 py-0.5 mb-0.5 truncate" title="{{ $cuti->user->name }}">
                                        {{ Str::limit($cuti->user->name, 10) }}
                                    </div>
                                @endforeach
                                @if (count($cutiHariItu) > 2)
                                    <div class="text-gray-400">+{{ count($cutiHariItu) - 2 }} lagi</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Sidebar: yang sedang cuti hari ini --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Sedang Cuti Hari Ini</h3>

                    @forelse ($sedangCutiHariIni as $cuti)
                        <div class="border-b py-3 last:border-b-0">
                            <p class="font-medium text-sm text-gray-800">{{ $cuti->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $cuti->leaveType->nama_cuti }}</p>
                            <p class="text-xs text-gray-400">
                                s/d {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada yang sedang cuti hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>