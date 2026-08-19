<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Jenis Cuti</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('admin.leave-types.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                    + Tambah Jenis Cuti
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Nama Cuti</th>
                            <th class="px-6 py-3">Jatah Default</th>
                            <th class="px-6 py-3">Perlu Lampiran</th>
                            <th class="px-6 py-3">Mengurangi Saldo</th>
                            <th class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaveTypes as $type)
                            <tr class="border-t">
                                <td class="px-6 py-4">{{ $type->nama_cuti }}</td>
                                <td class="px-6 py-4">{{ $type->jatah_hari_default }} hari</td>
                                <td class="px-6 py-4">{{ $type->perlu_lampiran ? 'Ya' : 'Tidak' }}</td>
                                <td class="px-6 py-4">{{ $type->mengurangi_saldo ? 'Ya' : 'Tidak' }}</td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="{{ route('admin.leave-types.edit', $type) }}" class="text-primary-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.leave-types.destroy', $type) }}" class="inline" onsubmit="return confirm('Yakin hapus jenis cuti ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>