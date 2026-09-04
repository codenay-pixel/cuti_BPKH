@php
    $namaApp = config('app.name', 'SICUTI');
    $satker  = \Illuminate\Support\Str::title(config('instansi.satker'));
    $kota    = config('instansi.kota');
@endphp

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk &mdash; {{ $namaApp }}</title>

        <link rel="icon" href="{{ asset('images/logo-kemenhut.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">

            <div class="hidden lg:flex lg:w-1/2 bg-primary-700 flex-col items-center justify-center text-white p-12">
                <div class="bg-white rounded-full p-6 mb-6">
                    <img src="{{ asset('images/logo-kemenhut.png') }}" alt="Logo Kementerian Kehutanan" class="w-[72px] h-[72px] object-contain">
                </div>

                <h1 class="text-4xl font-bold tracking-tight text-center">{{ $namaApp }}</h1>
                <p class="mt-2 text-lg text-primary-100 text-center">{{ config('instansi.tagline') }}</p>

                <p class="mt-8 pt-6 border-t border-primary-500/50 text-primary-100 text-center text-sm max-w-xs leading-relaxed">
                    {{ $satker }}{{ $kota ? ' ' . $kota : '' }}<br>
                    <span class="text-primary-200/80 text-xs">
                        {{ \Illuminate\Support\Str::title(config('instansi.direktorat')) }}
                    </span>
                </p>
            </div>

            <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 p-6">
                <div class="w-full max-w-md">

                    <div class="lg:hidden text-center mb-6">
                        <img src="{{ asset('images/logo-kemenhut.png') }}" alt="Logo Kementerian Kehutanan"
                             class="w-16 h-16 object-contain mx-auto">
                        <h1 class="mt-3 text-2xl font-bold tracking-tight text-primary-700">{{ $namaApp }}</h1>
                        <p class="text-sm text-gray-600">{{ config('instansi.tagline') }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">{{ $satker }}{{ $kota ? ' ' . $kota : '' }}</p>
                    </div>

                    <div class="hidden lg:block mb-4">
                        <h2 class="text-xl font-semibold text-primary-700">Salam Hijau!</h2>
                        <p class="text-sm text-gray-500">Masuk dengan NIP dan password Anda.</p>
                    </div>

                    <div class="bg-white shadow-md rounded-xl p-6 sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-[11px] text-gray-400">
                        &copy; {{ date('Y') }} {{ \Illuminate\Support\Str::title(config('instansi.kementerian')) }}
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
