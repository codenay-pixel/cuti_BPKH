<section>
    <header class="pb-4 border-b border-gray-300">
        <h2 class="text-base font-semibold text-gray-900">Ubah Password</h2>
        <p class="mt-1 text-sm text-gray-500">Gunakan password yang panjang dan tidak dipakai di layanan lain.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('current_password', 'updatePassword') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="update_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                <input type="password" id="update_password" name="password" autocomplete="new-password"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('password', 'updatePassword') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Ulangi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('password_confirmation', 'updatePassword') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button class="bg-primary-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-emerald-600">Password diperbarui.</p>
            @endif
        </div>
    </form>
</section>
