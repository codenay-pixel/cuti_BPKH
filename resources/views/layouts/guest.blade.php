<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">

            <!-- Panel Kiri: Hijau BPKH -->
            <div class="hidden lg:flex lg:w-1/2 bg-primary-700 flex-col items-center justify-center text-white p-12">
                <div class="bg-white rounded-full p-6 mb-6">
                    <img src="{{ asset('images/logo-kemenhut.png') }}" alt="Logo Kementerian Kehutanan" class="w-[72px] h-[72px]">
                </div>
                <h1 class="text-2xl font-semibold text-center mb-2">Sistem Manajemen Cuti Pegawai</h1>
                <p class="text-primary-100 text-center text-sm max-w-xs">
                    Balai Pemantapan Kawasan Hutan Wilayah 1 Medan
                </p>
            </div>

            <!-- Panel Kanan: Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 p-6">
                <div class="w-full max-w-md">

                    <!-- Logo kecil, tampil hanya di layar kecil (mobile) -->
                    <div class="lg:hidden flex justify-center mb-6">
                        <img src="{{ asset('images/logo-kemenhut.png') }}" alt="Logo Kementerian Kehutanan" class="w-16 h-16">
                    </div>

                    <div class="mb-4 text-center lg:text-left">
                        <h2 class="text-xl font-semibold text-primary-700">Salam Hijau!</h2>
                    </div>

                    <div class="bg-white shadow-md rounded-lg p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>