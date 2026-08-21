@php $u = auth()->user(); @endphp

<section>
    <header class="pb-4 border-b border-gray-300">
        <h2 class="text-base font-semibold text-gray-900">Data Kepegawaian</h2>
        <p class="mt-1 text-sm text-gray-500">
            Perubahan NIP, jabatan, unit kerja, dan atasan langsung dilakukan oleh Admin Kepegawaian.
        </p>
    </header>

    <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
        <div>
            <dt class="text-gray-500">Nama</dt>
            <dd class="font-medium text-gray-900">{{ $u->name }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">NIP</dt>
            <dd class="font-medium text-gray-900 font-mono">{{ $u->nip_formatted }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Jabatan</dt>
            <dd class="font-medium text-gray-900">{{ $u->jabatan ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Unit Kerja</dt>
            <dd class="font-medium text-gray-900">{{ $u->unit_kerja ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Peran</dt>
            <dd class="font-medium text-gray-900">{{ $u->role_label }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Atasan Langsung</dt>
            <dd class="font-medium text-gray-900">{{ $u->atasan->name ?? 'Belum diatur' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">TMT PNS</dt>
            <dd class="font-medium text-gray-900">{{ $u->tmt_pns?->translatedFormat('d F Y') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Masa Kerja</dt>
            <dd class="font-medium text-gray-900">{{ $u->masa_kerja }}</dd>
        </div>
    </dl>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 pt-5 border-t border-gray-300 space-y-4">
        @csrf @method('PATCH')

        <p class="text-sm font-medium text-gray-700">Data yang dapat Anda ubah sendiri:</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Telepon</label>
                <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', $u->no_telp) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('no_telp') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $u->email) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button class="bg-primary-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                Simpan
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-emerald-600">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
