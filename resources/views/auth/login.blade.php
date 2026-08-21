<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- NIP -->
        <div>
            <x-input-label for="nip" :value="__('NIP')" />
            <x-text-input id="nip"
                          class="block mt-1 w-full tracking-wide"
                          type="text"
                          name="nip"
                          :value="old('nip')"
                          inputmode="numeric"
                          maxlength="18"
                          placeholder="Contoh: 199003032010012003"
                          required autofocus autocomplete="username" />
            <p class="text-xs text-gray-500 mt-1">Masukkan NIP tanpa spasi atau tanda baca.</p>
            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>

        <p class="mt-6 text-xs text-center text-gray-500">
            Lupa password? Hubungi admin kepegawaian untuk reset.
        </p>
    </form>
</x-guest-layout>
