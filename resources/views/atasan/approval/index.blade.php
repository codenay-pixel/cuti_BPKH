<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approval Cuti (Atasan)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6 space-y-4">
                @forelse ($pengajuan as $item)
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium text-gray-800">{{ $item->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $item->leaveType->nama_cuti }}</p>
                            </div>
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                Menunggu
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 mb-1">
                            {{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_selesai->format('d M Y') }}
                            ({{ $item->jumlah_hari }} hari)
                        </p>
                        <p class="text-sm text-gray-600 mb-4">Alasan: {{ $item->alasan }}</p>
                        @if ($item->lampiran)
                        <p class="text-sm mb-4">
                        <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="text-primary-600 hover:underline">
                            📎 Lihat Lampiran
                        </a>
                        </p>
                        @endif

                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('approval.approve', $item) }}">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                    Setujui
                                </button>
                            </form>

                            <form method="POST" action="{{ route('approval.reject', $item) }}" onsubmit="return prompt('Alasan penolakan:') !== null ? (this.catatan.value = prompt !== null) : false;">
                                @csrf
                                <input type="hidden" name="catatan" value="Ditolak oleh atasan">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">Tidak ada pengajuan cuti yang menunggu persetujuan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>