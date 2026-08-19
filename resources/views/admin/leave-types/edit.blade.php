<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Jenis Cuti</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6">
                <form method="POST" action="{{ route('admin.leave-types.update', $leaveType) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Cuti</label>
                        <input type="text" name="nama_cuti" value="{{ old('nama_cuti', $leaveType->nama_cuti) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('nama_cuti') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jatah Hari Default (per tahun)</label>
                        <input type="number" name="jatah_hari_default" value="{{ old('jatah_hari_default', $leaveType->jatah_hari_default) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('jatah_hari_default') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4 flex items-center gap-2">
                        <input type="checkbox" name="perlu_lampiran" value="1" id="perlu_lampiran" {{ old('perlu_lampiran', $leaveType->perlu_lampiran) ? 'checked' : '' }}>
                        <label for="perlu_lampiran" class="text-sm text-gray-700">Perlu lampiran (misal surat dokter)</label>
                    </div>

                    <div class="mb-6 flex items-center gap-2">
                        <input type="checkbox" name="mengurangi_saldo" value="1" id="mengurangi_saldo" {{ old('mengurangi_saldo', $leaveType->mengurangi_saldo) ? 'checked' : '' }}>
                        <label for="mengurangi_saldo" class="text-sm text-gray-700">Mengurangi saldo cuti tahunan</label>
                    </div>

                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">Update</button>
                    <a href="{{ route('admin.leave-types.index') }}" class="ml-2 text-gray-500 text-sm hover:underline">Batal</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>