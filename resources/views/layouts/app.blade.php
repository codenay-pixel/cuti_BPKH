<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'SICUTI') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-50">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b border-gray-300">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    @if (session('success'))
                        <div class="mb-4 flex items-start gap-2.5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                            <x-ikon nama="centang" kelas="w-5 h-5 shrink-0 mt-px" />
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any() && ! request()->routeIs('leave.create'))
                        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{ $slot }}
            </main>

            <footer class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }}
                {{ \Illuminate\Support\Str::title(config('instansi.satker')) }} &mdash;
                {{ \Illuminate\Support\Str::title(config('instansi.direktorat')) }},
                {{ \Illuminate\Support\Str::title(config('instansi.kementerian')) }}
            </footer>
        </div>
    </body>
</html>
