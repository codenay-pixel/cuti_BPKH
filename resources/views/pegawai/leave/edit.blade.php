<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ubah Pengajuan Cuti</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            Diajukan {{ $leaveRequest->created_at->translatedFormat('d F Y, H:i') }} {{ config('instansi.zona_waktu') }}
            &middot; masih menunggu keputusan atasan langsung
        </p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="flex items-start gap-2.5 p-4 bg-sky-50 border border-sky-200 text-sky-900 rounded-lg text-sm">
            <x-ikon nama="jam" kelas="w-5 h-5 shrink-0 mt-px" />
            <span>
                Perubahan hanya bisa dilakukan selama pengajuan belum diputuskan atasan langsung.
                Setelah disetujui atau ditolak, isinya terkunci.
            </span>
        </div>
    </div>

    @include('pegawai.leave._form', ['leaveRequest' => $leaveRequest])
</x-app-layout>
