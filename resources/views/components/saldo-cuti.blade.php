@props(['saldo', 'tertahan' => 0, 'ringkas' => false])

@php
    $tersedia = $saldo['total_tersedia'] - $tertahan;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-gray-300 rounded-xl overflow-hidden']) }}>
    <div class="flex items-center justify-between px-5 py-3 bg-primary-600 text-white">
        <div>
            <h3 class="font-semibold text-sm">Saldo Cuti Tahunan {{ $saldo['tahun'] }}</h3>
            <p class="text-[11px] text-primary-100">Hak 12 hari/tahun &middot; sisa 2 tahun terakhir maks. 6 hari masing-masing</p>
        </div>
        <div class="text-right">
            <div class="text-3xl font-bold leading-none">{{ max(0, $tersedia) }}</div>
            <div class="text-[11px] text-primary-100">hari dapat diambil</div>
        </div>
    </div>

    <div class="grid grid-cols-3 divide-x divide-gray-400 border-b border-gray-400">
        @foreach ($saldo['rincian'] as $baris)
            <div class="px-3 py-4 text-center">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ $baris['label'] }}</p>
                <p class="mt-1 text-2xl font-bold {{ $baris['tersedia'] > 0 ? 'text-primary-700' : 'text-gray-300' }}">
                    {{ $baris['tersedia'] }}
                </p>
                <p class="text-[11px] text-gray-400">
                    dari {{ $baris['sisa'] }} sisa &middot; {{ $baris['catatan'] }}
                </p>
                @unless ($baris['ada'])
                    <p class="text-[10px] text-amber-600 mt-1">belum diisi kepegawaian</p>
                @endunless
            </div>
        @endforeach
    </div>

    @unless ($ringkas)
        <div class="px-5 py-3 text-xs text-gray-600 bg-gray-50 space-y-1">
            <div class="flex justify-between">
                <span>Terpakai tahun {{ $saldo['tahun'] }}</span>
                <span class="font-medium">{{ $saldo['terpakai_tahun_ini'] }} hari</span>
            </div>
            @if ($tertahan > 0)
                <div class="flex justify-between text-amber-700">
                    <span>Sedang dalam proses persetujuan</span>
                    <span class="font-medium">{{ $tertahan }} hari</span>
                </div>
            @endif
            <div class="flex justify-between pt-1 border-t border-gray-300 font-semibold text-gray-800">
                <span>Sisa yang dapat diajukan</span>
                <span>{{ max(0, $tersedia) }} hari</span>
            </div>
        </div>
    @endunless
</div>
