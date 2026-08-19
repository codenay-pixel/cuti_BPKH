<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Saldo Cuti ({{ now()->year }})</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('admin.leave-balances.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                    + Set Saldo Cuti
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Pegawai</th>
                            <th class="px-6 py-3">Jenis Cuti</th>
                            <th class="px-6 py-3">Jatah</th>
                            <th class="px-6 py-3">Terpakai</th>
                            <th class="px-6 py-3">Sisa</th>
                            <th class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($balances as $balance)
                            <tr class="border-t">
                                <td class="px-6 py-4">{{ $balance->user->name }}</td>
                                <td class="px-6 py-4">{{ $balance->leaveType->nama_cuti }}</td>
                                <td class="px-6 py-4">{{ $balance->jatah }}</td>
                                <td class="px-6 py-4">{{ $balance->terpakai }}</td>
                                <td class="px-6 py-4 font-medium">{{ $balance->sisa }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.leave-balances.edit', $balance) }}" class="text-primary-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $balances->links() }}
            </div>
        </div>
    </div>
</x-app-layout>