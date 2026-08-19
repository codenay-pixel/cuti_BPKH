<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Pengajuan Cuti
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6 space-y-3">

                <div>
                    <p class="text-sm text-gray-500">Jenis Cuti</p>
                    <p class="font-medium">{{ $leaveRequest->leaveType->nama_cuti }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Tanggal</p>
                    <p class="font-medium">
                        {{ $leaveRequest->tanggal_mulai->format('d M Y') }} - {{ $leaveRequest->tanggal_selesai->format('d M Y') }}
                        ({{ $leaveRequest->jumlah_hari }} hari)
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Alasan</p>
                    <p class="font-medium">{{ $leaveRequest->alasan }}</p>
                </div>
                @if ($leaveRequest->lampiran)
                <div>
                <p class="text-sm text-gray-500">Lampiran</p>
                <a href="{{ asset('storage/' . $leaveRequest->lampiran) }}" target="_blank" class="text-primary-600 text-sm hover:underline">
                Lihat/Unduh Lampiran
                </a>
                </div>
                @endif

                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="inline-block px-2 py-1 rounded-full text-xs
                        @if ($leaveRequest->status === 'menunggu') bg-yellow-100 text-yellow-700
                        @elseif ($leaveRequest->status === 'disetujui') bg-green-100 text-green-700
                        @elseif ($leaveRequest->status === 'ditolak') bg-red-100 text-red-700
                        @else bg-blue-100 text-blue-700
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $leaveRequest->status)) }}
                    </span>
                </div>

                @if ($leaveRequest->approvals->isNotEmpty())
                    <div class="pt-4 border-t">
                        <p class="text-sm text-gray-500 mb-2">Riwayat Persetujuan</p>
                        @foreach ($leaveRequest->approvals as $approval)
                            <p class="text-sm text-gray-600">
                                {{ ucfirst($approval->level) }} — {{ $approval->approver->name }}:
                                <span class="font-medium">{{ ucfirst($approval->keputusan) }}</span>
                                @if ($approval->catatan)
                                    ({{ $approval->catatan }})
                                @endif
                            </p>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('leave.index') }}" class="inline-block mt-4 text-primary-600 text-sm hover:underline">
                    ← Kembali ke riwayat
                </a>
            </div>
        </div>
    </div>
</x-app-layout>