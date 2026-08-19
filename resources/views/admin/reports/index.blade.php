<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Seluruh Pengajuan Cuti</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

           <form method="GET" class="mb-4 flex gap-2 flex-wrap items-center">
    <input type="text" name="nama" value="{{ request('nama') }}" placeholder="Cari nama pegawai..." class="rounded-md border-gray-300 shadow-sm text-sm">
    <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm">
        <option value="">Semua Status</option>
        <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
        <option value="disetujui_atasan" {{ request('status') === 'disetujui_atasan' ? 'selected' : '' }}>Disetujui Atasan</option>
        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
    </select>
    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md text-sm hover:bg-primary-700">Filter</button>
    <a href="{{ route('admin.reports.index') }}" class="text-gray-500 text-sm self-center hover:underline">Reset</a>

    <span class="border-l h-6 mx-2"></span>

    <a href="{{ route('admin.reports.export-excel', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
        Export Excel
    </a>
    <a href="{{ route('admin.reports.export-pdf', request()->query()) }}" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
        Export PDF
    </a>
</form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Pegawai</th>
                            <th class="px-6 py-3">Jenis Cuti</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Hari</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $item)
                            <tr class="border-t">
                                <td class="px-6 py-4">{{ $item->user->name }}</td>
                                <td class="px-6 py-4">{{ $item->leaveType->nama_cuti }}</td>
                                <td class="px-6 py-4">{{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_selesai->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $item->jumlah_hari }}</td>
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
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>
</x-app-layout>