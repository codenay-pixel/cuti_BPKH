<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profil Saya</h2>
        <p class="text-sm text-gray-500 mt-0.5">Data kepegawaian dan keamanan akun</p>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-gray-300 rounded-xl p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white border border-gray-300 rounded-xl p-6">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
