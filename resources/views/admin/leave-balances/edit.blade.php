<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Atur Saldo Cuti Tahunan</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $user->name }} &middot; NIP {{ $user->nip_formatted }} &middot; {{ $user->jabatan ?? $user->role_label }}
        </p>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('admin.leave-balances.update', $user) }}"
                  class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                @csrf @method('PUT')

                <div class="px-5 py-4 border-b border-gray-300 bg-gray-50">
                    <h3 class="font-semibold text-sm text-gray-800">Tiga tahun sekaligus</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Isi <strong>jatah</strong> (hak cuti tahun itu, umumnya 12) dan <strong>terpakai</strong>
                        (yang sudah diambil, termasuk yang tercatat di kertas).
                    </p>
                </div>

                <div class="divide-y divide-gray-300">
                    @foreach ($ringkasan['tahun'] as $t => $d)
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="font-semibold text-gray-800">Tahun {{ $t }}</span>
                                    @if ($d['berjalan'])
                                        <span class="ms-2 px-2 py-0.5 rounded text-[10px] bg-primary-100 text-primary-700">tahun berjalan</span>
                                    @else
                                        <span class="ms-2 px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-600">maks. 6 hari terpakai</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] text-gray-400">dapat diambil {{ $tahun }}</span>
                                    <span class="ms-1 text-lg font-bold {{ $d['tersedia'] > 0 ? 'text-primary-700' : 'text-gray-300' }}">
                                        {{ $d['tersedia'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="jatah-{{ $t }}" class="block text-xs font-medium text-gray-600 mb-1">Jatah (hari)</label>
                                    <input type="number" id="jatah-{{ $t }}" name="tahun[{{ $t }}][jatah]"
                                           min="0" max="60" required
                                           value="{{ old('tahun.' . $t . '.jatah', $d['jatah']) }}"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label for="terpakai-{{ $t }}" class="block text-xs font-medium text-gray-600 mb-1">Terpakai (hari)</label>
                                    <input type="number" id="terpakai-{{ $t }}" name="tahun[{{ $t }}][terpakai]"
                                           min="0" max="60" required
                                           value="{{ old('tahun.' . $t . '.terpakai', $d['terpakai']) }}"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                            </div>

                            @if ($d['dibatasi'])
                                <p class="mt-2 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1.5">
                                    Sisa {{ $d['sisa'] }} hari, tetapi hanya {{ $d['tersedia'] }} yang boleh dipakai pada {{ $tahun }}
                                    sesuai PP 11/2017 Pasal 313.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="px-5 py-4 border-t border-gray-300 bg-gray-50 flex flex-wrap items-center gap-3">
                    <button class="bg-primary-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                        Simpan
                    </button>
                    <a href="{{ route('admin.leave-balances.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-800">Batal</a>

                    <span class="sm:ms-auto text-sm text-gray-600">
                        Total dapat diambil {{ $tahun }}:
                        <strong class="text-gray-900">{{ $ringkasan['total'] }} hari</strong>
                    </span>
                </div>
            </form>

            <p class="mt-4 text-xs text-gray-400 text-center">
                Kolom "terpakai" hanya untuk koreksi data. Cuti yang disetujui lewat aplikasi
                memotong saldo secara otomatis, dimulai dari tahun paling lama.
            </p>
        </div>
    </div>
</x-app-layout>
