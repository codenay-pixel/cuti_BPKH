<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah / Ubah Saldo Cuti</h2>
        <p class="text-sm text-gray-500 mt-0.5">Bila saldo pegawai untuk tahun tersebut sudah ada, jatahnya akan diperbarui.</p>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.leave-balances.store') }}"
                  class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
                @csrf

                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1.5">Pegawai <span class="text-rose-500">*</span></label>
                    <select id="user_id" name="user_id" required class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">— Pilih pegawai —</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} — {{ $user->nip }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="leave_type_id" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Cuti <span class="text-rose-500">*</span></label>
                    <select id="leave_type_id" name="leave_type_id" required class="w-full rounded-lg border-gray-300 text-sm">
                        @foreach ($leaveTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->nama_cuti }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hanya Cuti Tahunan yang memiliki saldo.</p>
                    @error('leave_type_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun <span class="text-rose-500">*</span></label>
                        <select id="tahun" name="tahun" required class="w-full rounded-lg border-gray-300 text-sm">
                            @for ($t = now()->year + 1; $t >= now()->year - 4; $t--)
                                <option value="{{ $t }}" @selected(old('tahun', now()->year) == $t)>{{ $t }}</option>
                            @endfor
                        </select>
                        @error('tahun') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jatah" class="block text-sm font-medium text-gray-700 mb-1.5">Jatah (hari) <span class="text-rose-500">*</span></label>
                        <input type="number" id="jatah" name="jatah" min="0" max="60" required
                               value="{{ old('jatah', 12) }}" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('jatah') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button class="bg-primary-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">Simpan</button>
                    <a href="{{ route('admin.leave-balances.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
