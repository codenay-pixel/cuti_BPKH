<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto p-6">

                <h3 class="text-lg font-medium text-gray-800 mb-2">
                    Selamat datang, {{ auth()->user()->name }}
                </h3>
                <p class="text-sm text-gray-500 mb-6">
                    {{ auth()->user()->jabatan ?? ucfirst(auth()->user()->role) }}
                </p>

                @if (auth()->user()->isPegawai())
                    <div class="p-4 bg-blue-50 rounded-lg mb-4">
                        <p class="text-sm text-gray-700 mb-2">Kelola pengajuan cuti Anda di sini.</p>
                        <a href="{{ route('leave.create') }}" class="text-primary-600 text-sm font-medium hover:underline">
                            + Ajukan cuti baru
                        </a>
                    </div>
                @endif

                @if (auth()->user()->isAtasan())
                    <div class="p-4 bg-yellow-50 rounded-lg mb-4">
                        <p class="text-sm text-gray-700 mb-2">Ada pengajuan cuti tim Anda yang menunggu persetujuan.</p>
                        <a href="{{ route('approval.index') }}" class="text-primary-600 text-sm font-medium hover:underline">
                            Lihat daftar approval
                        </a>
                    </div>
                @endif

                @if (auth()->user()->isAdmin())
                    <div class="p-4 bg-green-50 rounded-lg mb-4">
                        <p class="text-sm text-gray-700 mb-2">Finalisasi pengajuan cuti yang sudah disetujui atasan.</p>
                        <a href="{{ route('admin.approval.index') }}" class="text-primary-600 text-sm font-medium hover:underline">
                            Lihat daftar finalisasi
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>