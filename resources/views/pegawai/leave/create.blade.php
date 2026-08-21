<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Formulir Pengajuan Cuti</h2>
        <p class="text-sm text-gray-500 mt-0.5">Isi data berikut. Formulir resmi akan dibuat otomatis setelah disetujui.</p>
    </x-slot>

    @include('pegawai.leave._form', ['leaveRequest' => null])
</x-app-layout>
