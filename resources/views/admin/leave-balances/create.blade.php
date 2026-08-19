<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Set Saldo Cuti</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6">
                <form method="POST" action="{{ route('admin.leave-balances.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai</label>
                        <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih pegawai --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Cuti</label>
                        <select name="leave_type_id" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih jenis cuti --</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->nama_cuti }}</option>
                            @endforeach
                        </select>
                        @error('leave_type_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <input type="number" name="tahun" value="{{ old('tahun', now()->year) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('tahun') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jatah (hari)</label>
                        <input type="number" name="jatah" value="{{ old('jatah', 12) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('jatah') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">Simpan</button>
                    <a href="{{ route('admin.leave-balances.index') }}" class="ml-2 text-gray-500 text-sm hover:underline">Batal</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>