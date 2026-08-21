<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Data Pegawai</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $user->name }} &middot; NIP {{ $user->nip_formatted }}</p>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data"
                  class="bg-white border border-gray-300 rounded-xl p-6 space-y-5">
                @csrf @method('PUT')
                @include('admin.users._form', ['user' => $user, 'atasanList' => $atasanList])

                <div class="flex items-center gap-3 pt-2">
                    <button class="bg-primary-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
