<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Saldo Cuti</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6">

                <div class="mb-4 p-3 bg-gray-50 rounded text-sm text-gray-600">
                    {{ $leaveBalance->user->name }} — {{ $leaveBalance->leaveType->nama_cuti }} ({{ $leaveBalance->tahun }})
                </div>

                <form method="POST" action="{{ route('admin.leave-balances.update', $leaveBalance) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jatah (hari)</label>
                        <input type="number" name="jatah" value="{{ old('jatah', $leaveBalance->jatah) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('jatah') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Terpakai (hari)</label>
                        <input type="number" name="terpakai" value="{{ old('terpakai', $leaveBalance->terpakai) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('terpakai') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">Update</button>
                    <a href="{{ route('admin.leave-balances.index') }}" class="ml-2 text-gray-500 text-sm hover:underline">Batal</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>