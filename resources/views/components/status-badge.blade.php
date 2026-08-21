@props(['status'])

@php
    $peta = [
        'menunggu'         => ['Menunggu Atasan', 'bg-amber-100 text-amber-800 ring-amber-200'],
        'disetujui_atasan' => ['Menunggu Pejabat', 'bg-sky-100 text-sky-800 ring-sky-200'],
        'disetujui'        => ['Disetujui', 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        'ditolak'          => ['Ditolak', 'bg-rose-100 text-rose-800 ring-rose-200'],
    ];
    [$label, $warna] = $peta[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'bg-gray-100 text-gray-700 ring-gray-200'];
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium ring-1 ring-inset whitespace-nowrap {{ $warna }}">
    {{ $label }}
</span>
