<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ajukan Cuti
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6">

                {{-- Info saldo cuti --}}
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-medium text-sm text-gray-700 mb-2">Sisa Cuti Anda</h3>
                    @forelse ($saldo as $item)
                        <p class="text-sm text-gray-600">
                            {{ $item->leaveType->nama_cuti }}: <span class="font-semibold">{{ $item->sisa }} hari</span>
                        </p>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada saldo cuti untuk tahun ini.</p>
                    @endforelse
                </div>

                {{-- Error saldo dari controller --}}
                @if ($errors->has('saldo'))
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                        {{ $errors->first('saldo') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Cuti</label>
                        <select name="leave_type_id" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih jenis cuti --</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->nama_cuti }}
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm">
                            @error('tanggal_mulai')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm">
                            @error('tanggal_selesai')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                        <textarea name="alasan" rows="4" class="w-full rounded-md border-gray-300 shadow-sm">{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lampiran (opsional — surat dokter, dsb)
                        </label>
                        <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-600 border border-gray-300 rounded-md p-2">
                        <p class="text-xs text-gray-500 mt-1">Format PDF/JPG/PNG, maksimal 2MB.</p>
                        @error('lampiran')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                        Ajukan Cuti
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>