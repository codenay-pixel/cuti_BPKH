@props(['approval' => null, 'menunggu' => false])

@if ($approval && $approval->keputusan === 'disetujui')
    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-emerald-500 text-white whitespace-nowrap"
          title="{{ $approval->approver?->name }} &middot; {{ $approval->tanggal_keputusan?->translatedFormat('d M Y H:i') }}">
        <x-ikon nama="centang" kelas="w-3 h-3" /> Diterima
    </span>
@elseif ($approval && $approval->keputusan === 'ditolak')
    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-rose-500 text-white whitespace-nowrap"
          title="{{ $approval->catatan }}">
        <x-ikon nama="silang" kelas="w-3 h-3" /> Ditolak
    </span>
@elseif ($menunggu)
    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-amber-100 text-amber-800 whitespace-nowrap">
        <x-ikon nama="jam" kelas="w-3 h-3" /> Menunggu
    </span>
@else
    <span class="text-gray-300 text-xs">&mdash;</span>
@endif
