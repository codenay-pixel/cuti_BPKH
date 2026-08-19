<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Riwayat Pengajuan Cuti
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('leave.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                    + Ajukan Cuti
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Jenis Cuti</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Jumlah Hari</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $item)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('leave.show', $item) }}" class="text-primary-600 hover:underline">
                                        {{ $item->leaveType->nama_cuti }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">{{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $item->jumlah_hari }} hari</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs
                                        @if ($item->status === 'menunggu') bg-yellow-100 text-yellow-700
                                        @elseif ($item->status === 'disetujui') bg-green-100 text-green-700
                                        @elseif ($item->status === 'ditolak') bg-red-100 text-red-700
                                        @else bg-blue-100 text-blue-700
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada pengajuan cuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>