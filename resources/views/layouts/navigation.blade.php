@php
    $u = auth()->user();
    $namaApp = config('app.name', 'SICUTI');
    $satker = \Illuminate\Support\Str::title(config('instansi.satker'));
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-300 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-kemenhut.png') }}" alt="Logo Kementerian Kehutanan" class="h-9 w-9 object-contain">
                        <span class="hidden lg:block leading-tight">
                            <span class="block text-sm font-bold tracking-tight text-gray-800">{{ $namaApp }}</span>
                            <span class="block text-[10px] text-gray-500">{{ $satker }} {{ config('instansi.kota') }}</span>
                        </span>
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Beranda
                    </x-nav-link>

                    <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                        Kalender Kantor
                    </x-nav-link>

                    <x-nav-link :href="route('leave.index')" :active="request()->routeIs('leave.*')">
                        Cuti Saya
                    </x-nav-link>

                    @if ($u->isAtasanLangsung())
                        <x-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">
                            Persetujuan
                        </x-nav-link>
                    @endif

                    @if ($u->isKepalaBalai())
                        <x-nav-link :href="route('kepala-balai.approval.index')" :active="request()->routeIs('kepala-balai.approval.*')">
                            Persetujuan Final
                        </x-nav-link>
                    @endif

                    @if ($u->isAdmin())
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            Kelola Pegawai
                        </x-nav-link>
                        <x-nav-link :href="route('admin.leave-balances.index')" :active="request()->routeIs('admin.leave-balances.*')">
                            Saldo Cuti
                        </x-nav-link>
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                            Rekap
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-900 focus:outline-none transition">
                            <div class="text-right leading-tight">
                                <div>{{ $u->name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $u->role_label }}</div>
                            </div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs text-gray-500">NIP</p>
                            <p class="text-xs font-medium text-gray-800">{{ $u->nip_formatted }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            Profil Saya
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Beranda</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">Kalender Kantor</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leave.index')" :active="request()->routeIs('leave.*')">Cuti Saya</x-responsive-nav-link>

            @if ($u->isAtasanLangsung())
                <x-responsive-nav-link :href="route('approval.index')" :active="request()->routeIs('approval.*')">Persetujuan</x-responsive-nav-link>
            @endif

            @if ($u->isKepalaBalai())
                <x-responsive-nav-link :href="route('kepala-balai.approval.index')" :active="request()->routeIs('kepala-balai.approval.*')">Persetujuan Final</x-responsive-nav-link>
            @endif

            @if ($u->isAdmin())
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Kelola Pegawai</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.leave-balances.index')" :active="request()->routeIs('admin.leave-balances.*')">Saldo Cuti</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">Rekap</x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-300">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ $u->name }}</div>
                <div class="font-medium text-sm text-gray-500">NIP {{ $u->nip_formatted }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profil Saya</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Keluar
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
