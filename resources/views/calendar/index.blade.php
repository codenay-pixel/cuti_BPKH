<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kalender Kantor</h2>
                <p class="text-sm text-gray-500 mt-0.5">Klik tanggal mana pun untuk melihat siapa yang cuti atau dinas hari itu</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('buka-acara'))"
                    class="inline-flex items-center justify-center gap-1.5 bg-accent-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-accent-600 transition w-full sm:w-auto">
                <span class="text-lg leading-none">+</span> Acara Baru
            </button>
        </div>
    </x-slot>

    <div class="pb-12" x-data="kalender()" @buka-acara.window="modal = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- ==================== KALENDER ==================== --}}
                <div class="lg:col-span-2 bg-white border border-gray-300 rounded-xl p-4 sm:p-5">

                    @php
                        $prev = \Carbon\Carbon::create($tahun, $bulan, 1)->subMonth();
                        $next = \Carbon\Carbon::create($tahun, $bulan, 1)->addMonth();
                        $hariIni = now()->format('Y-m-d');
                    @endphp

                    <div class="flex items-center justify-between mb-4 gap-2">
                        <a href="{{ route('calendar.index', ['bulan' => $prev->month, 'tahun' => $prev->year]) }}"
                           class="shrink-0 px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50"
                           aria-label="Bulan sebelumnya">&larr;</a>

                        <div class="text-center min-w-0">
                            <h3 class="font-semibold text-base sm:text-lg text-gray-800 truncate">
                                {{ $awalBulan->translatedFormat('F Y') }}
                            </h3>
                            @if (! $awalBulan->isSameMonth(now()))
                                <a href="{{ route('calendar.index') }}" class="text-xs text-primary-600 hover:underline">ke bulan ini</a>
                            @endif
                        </div>

                        <a href="{{ route('calendar.index', ['bulan' => $next->month, 'tahun' => $next->year]) }}"
                           class="shrink-0 px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50"
                           aria-label="Bulan berikutnya">&rarr;</a>
                    </div>

                    <div class="flex items-center gap-4 mb-3 text-[11px] text-gray-600">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-primary-500"></span> Cuti</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-accent-500"></span> Dinas / Acara</span>
                    </div>

                    {{-- ---------- GRID: tablet & desktop ---------- --}}
                    <div class="hidden sm:block">
                        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-gray-500 mb-1">
                            <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                        </div>

                        <div class="grid grid-cols-7 gap-1">
                            @foreach ($awalGrid->daysUntil($akhirGrid) as $tanggal)
                                @php
                                    $key = $tanggal->format('Y-m-d');
                                    $isBulanIni = $tanggal->month === $bulan;
                                    $isi = $agenda[$key] ?? [];
                                    $akhirPekan = $tanggal->isWeekend();
                                @endphp
                                <button type="button" @click="pilih('{{ $key }}')"
                                        class="text-left border rounded-lg min-h-[92px] p-1.5 text-[11px] transition
                                               focus:outline-none focus:ring-2 focus:ring-primary-400
                                               {{ $isBulanIni ? ($akhirPekan ? 'bg-gray-50' : 'bg-white') : 'bg-gray-50/60 text-gray-300' }}
                                               {{ $key === $hariIni ? 'border-primary-500' : 'border-gray-300' }}
                                               hover:border-primary-400 hover:shadow-sm"
                                        :class="terpilih === '{{ $key }}' ? 'ring-2 ring-primary-500 border-primary-500' : ''">

                                    <span class="flex items-center justify-between mb-1">
                                        <span class="font-semibold {{ $akhirPekan && $isBulanIni ? 'text-rose-400' : '' }}">
                                            {{ $tanggal->day }}
                                        </span>
                                        @if ($key === $hariIni)
                                            <span class="text-[9px] px-1 rounded bg-primary-600 text-white">hari ini</span>
                                        @endif
                                    </span>

                                    @foreach (array_slice($isi, 0, 3) as $item)
                                        <span class="block rounded px-1 py-0.5 mb-0.5 truncate
                                            {{ $item['tipe'] === 'cuti' ? 'bg-primary-100 text-primary-800' : 'bg-accent-500/20 text-accent-600' }}">
                                            {{ \Illuminate\Support\Str::limit($item['nama'], 12) }}
                                        </span>
                                    @endforeach

                                    @if (count($isi) > 3)
                                        <span class="block text-gray-400">+{{ count($isi) - 3 }} lagi</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <p class="mt-3 text-[11px] text-gray-400 text-center">
                            Klik tanggal untuk melihat rinciannya di panel sebelah kanan.
                        </p>
                    </div>

                    {{-- ---------- DAFTAR: HP ---------- --}}
                    <div class="sm:hidden">
                        @php
                            $agendaBulanIni = collect($agenda)
                                ->filter(fn ($v, $k) => \Carbon\Carbon::parse($k)->month === $bulan
                                    && \Carbon\Carbon::parse($k)->year === $tahun);
                        @endphp

                        @forelse ($agendaBulanIni as $tgl => $daftar)
                            @php $c = \Carbon\Carbon::parse($tgl); @endphp
                            <button type="button" @click="pilih('{{ $tgl }}')"
                                    class="w-full text-left flex gap-3 py-3 border-b border-gray-100 last:border-b-0">
                                <span class="shrink-0 w-12 text-center">
                                    <span class="block rounded-lg py-1.5 {{ $tgl === $hariIni ? 'bg-primary-600 text-white' : ($c->isWeekend() ? 'bg-rose-50 text-rose-500' : 'bg-gray-100 text-gray-700') }}">
                                        <span class="block text-[10px] uppercase leading-none">{{ $c->translatedFormat('D') }}</span>
                                        <span class="block text-lg font-bold leading-tight">{{ $c->day }}</span>
                                    </span>
                                </span>

                                <span class="min-w-0 flex-1 space-y-1.5 pt-0.5">
                                    @foreach ($daftar as $item)
                                        <span class="flex items-start gap-2">
                                            <span class="mt-1 shrink-0 w-2 h-2 rounded-full {{ $item['tipe'] === 'cuti' ? 'bg-primary-500' : 'bg-accent-500' }}"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-gray-800 leading-tight">{{ $item['nama'] }}</span>
                                                <span class="block text-xs text-gray-500">{{ $item['judul'] }}</span>
                                            </span>
                                        </span>
                                    @endforeach
                                </span>
                            </button>
                        @empty
                            <p class="py-10 text-center text-sm text-gray-500">
                                Tidak ada cuti atau agenda pada {{ $awalBulan->translatedFormat('F Y') }}.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- ==================== SIDEBAR ==================== --}}
                <div class="space-y-6">

                    {{-- Panel tanggal terpilih --}}
                    <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                        <div class="px-4 sm:px-5 py-3 border-b border-gray-300 bg-gray-50">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-sm text-gray-800" x-text="judulPanel"></h3>
                                    <p class="text-[11px] text-gray-500" x-text="labelTanggal"></p>
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-full text-[11px] font-medium"
                                      :class="daftar.length ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500'"
                                      x-text="daftar.length + ' orang'"></span>
                            </div>

                            <div class="flex items-center gap-2 mt-2">
                                <button type="button" @click="geser(-1)"
                                        class="px-2 py-1 rounded border border-gray-300 text-xs text-gray-600 hover:bg-white">&larr; Kemarin</button>
                                <button type="button" @click="pilih(hariIni)"
                                        class="px-2 py-1 rounded border border-gray-300 text-xs text-gray-600 hover:bg-white">Hari ini</button>
                                <button type="button" @click="geser(1)"
                                        class="px-2 py-1 rounded border border-gray-300 text-xs text-gray-600 hover:bg-white">Besok &rarr;</button>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-300 max-h-80 overflow-y-auto">
                            <template x-for="(item, i) in daftar" :key="i">
                                <div class="px-4 sm:px-5 py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate" x-text="item.nama"></p>
                                            <p class="text-xs text-gray-500" x-text="item.judul"></p>
                                            <p class="text-[11px] text-gray-400" x-text="item.ket"></p>
                                            <template x-if="item.lampiran">
                                                <a :href="item.lampiran" target="_blank"
                                                   class="inline-block mt-1 text-[11px] text-primary-600 hover:underline">Lihat surat dinas</a>
                                            </template>
                                        </div>
                                        <span class="shrink-0 px-2 py-0.5 rounded text-[10px]"
                                              :class="item.tipe === 'cuti' ? 'bg-primary-100 text-primary-700' : 'bg-accent-500/20 text-accent-600'"
                                              x-text="item.tipe === 'cuti' ? 'Cuti' : 'Dinas'"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="daftar.length === 0">
                                <p class="px-4 sm:px-5 py-8 text-sm text-gray-500 text-center">
                                    Tidak ada yang cuti atau dinas pada tanggal ini.
                                </p>
                            </template>
                        </div>
                    </div>

                    {{-- Agenda mendatang --}}
                    <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                        <div class="px-4 sm:px-5 py-3 border-b border-gray-300 flex items-center justify-between">
                            <h3 class="font-semibold text-sm text-gray-800">Agenda Mendatang</h3>
                            <button type="button" @click="modal = true" class="text-xs text-accent-600 hover:underline">+ Tambah</button>
                        </div>
                        <div class="divide-y divide-gray-300 max-h-96 overflow-y-auto">
                            @forelse ($acaraMendatang as $acara)
                                <div class="px-4 sm:px-5 py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800">{{ $acara->nama_acara }}</p>
                                            <p class="text-xs text-gray-500">{{ $acara->user->name }}</p>
                                            <p class="text-[11px] text-gray-400">
                                                {{ $acara->tanggal_mulai->translatedFormat('d M') }} &ndash;
                                                {{ $acara->tanggal_selesai->translatedFormat('d M Y') }}
                                                {{ $acara->lokasi ? ' · ' . $acara->lokasi : '' }}
                                            </p>
                                            @if ($acara->lampiran)
                                                <a href="{{ asset('storage/' . $acara->lampiran) }}" target="_blank"
                                                   class="inline-block mt-1 text-[11px] text-primary-600 hover:underline">
                                                    Lihat surat dinas
                                                </a>
                                            @endif
                                        </div>

                                        @if ($acara->user_id === auth()->id() || auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('events.destroy', $acara) }}"
                                                  onsubmit="return confirm('Hapus acara ini dari kalender?')">
                                                @csrf @method('DELETE')
                                                <button class="p-1.5 text-gray-300 hover:text-rose-500" title="Hapus acara">
                                                    <x-ikon nama="silang" kelas="w-4 h-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="px-4 sm:px-5 py-6 text-sm text-gray-500 text-center">Belum ada agenda mendatang.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== MODAL: TAMBAH ACARA ==================== --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
            <div class="fixed inset-0 bg-gray-900/50" @click="modal = false"></div>

            <div class="relative min-h-full flex items-end sm:items-start sm:justify-center sm:p-8">
                <div class="relative bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-xl shadow-xl sm:my-8"
                     @keydown.escape.window="modal = false">

                    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="px-5 sm:px-6 py-4 border-b border-gray-300 flex items-center justify-between gap-3 sticky top-0 bg-white rounded-t-2xl sm:rounded-t-xl">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-800">Tambah Acara Kalender</h3>
                                <p class="text-xs text-gray-500">Dinas luar kota, rapat, diklat, atau kegiatan lain</p>
                            </div>
                            <button type="button" @click="modal = false"
                                    class="shrink-0 text-gray-400 hover:text-gray-600 text-2xl leading-none px-1">&times;</button>
                        </div>

                        <div class="px-5 sm:px-6 py-5 space-y-4">
                            <div>
                                <label for="nama_acara" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Nama Acara <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="nama_acara" name="nama_acara" required maxlength="150"
                                       value="{{ old('nama_acara') }}"
                                       placeholder="Contoh: Dinas Luar — Verifikasi Tata Batas Kawasan"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </div>

                            <div x-data="{ jenisPilihan: '{{ old('jenis', 'dinas_luar') }}' }">
                                <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kegiatan</label>
                                <select id="jenis" name="jenis" x-model="jenisPilihan"
                                        class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @foreach (\App\Models\OfficeEvent::JENIS as $nilai => $label)
                                        <option value="{{ $nilai }}" @selected(old('jenis', 'dinas_luar') === $nilai)>{{ $label }}</option>
                                    @endforeach
                                </select>

                                <div x-show="jenisPilihan === 'lainnya'" x-cloak class="mt-2">
                                    <label for="jenis_lainnya" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Sebutkan Jenisnya <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" id="jenis_lainnya" name="jenis_lainnya" maxlength="100"
                                           value="{{ old('jenis_lainnya') }}"
                                           :required="jenisPilihan === 'lainnya'"
                                           placeholder="Contoh: Vaksinasi Kantor, Kerja Bakti, dll."
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                    @error('jenis_lainnya')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="ev_mulai" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Dari Tanggal <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" id="ev_mulai" name="tanggal_mulai" required
                                           @if (! $errors->any()) :value="modalTanggal" @endif
                                           value="{{ old('tanggal_mulai', now()->toDateString()) }}"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                </div>
                                <div>
                                    <label for="ev_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Sampai Tanggal <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" id="ev_selesai" name="tanggal_selesai" required
                                           @if (! $errors->any()) :value="modalTanggal" @endif
                                           value="{{ old('tanggal_selesai', now()->toDateString()) }}"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                                </div>
                            </div>

                            <div>
                                <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi</label>
                                <input type="text" id="lokasi" name="lokasi" maxlength="150" value="{{ old('lokasi') }}"
                                       placeholder="Contoh: Medan, Sumatera Utara"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </div>

                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1.5">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" rows="2" maxlength="1000"
                                          class="w-full rounded-lg border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500">{{ old('keterangan') }}</textarea>
                            </div>

                            <div>
                                <label for="ev_lampiran" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Foto / Scan Surat Dinas
                                </label>
                                <input type="file" id="ev_lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png" capture="environment"
                                       class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg p-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:bg-accent-500/10 file:text-accent-600">
                                <p class="text-xs text-gray-500 mt-1">
                                    PDF, JPG, atau PNG. Maksimal 2 MB. Dari HP bisa langsung memotret suratnya.
                                </p>
                            </div>
                        </div>

                        <div class="px-5 sm:px-6 py-4 border-t border-gray-300 flex items-center gap-3 bg-gray-50 sm:rounded-b-xl sticky bottom-0">
                            <button type="button" @click="modal = false"
                                    class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 sm:border-0">Batal</button>
                            <button type="submit"
                                    class="flex-1 sm:flex-none sm:ms-auto bg-accent-500 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-accent-600">
                                Simpan Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function kalender() {
            return {
                modal: false,
                agenda: @json($agenda),
                terpilih: @json($tanggalAwal),
                hariIni: @json(now()->format('Y-m-d')),
                batasAwal: @json($awalGrid->format('Y-m-d')),
                batasAkhir: @json($akhirGrid->format('Y-m-d')),

                pilih(tanggal) {
                    this.terpilih = tanggal;
                },

                /**
                 * Format tanggal lokal jadi YYYY-MM-DD.
                 * Jangan pakai toISOString(): itu mengubah ke UTC, sehingga di
                 * WIB (+7) tanggalnya mundur satu hari.
                 */
                format(d) {
                    const p = (n) => String(n).padStart(2, '0');
                    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
                },

                /** Geser tanggal terpilih maju/mundur, dibatasi rentang yang tampil. */
                geser(hari) {
                    const d = new Date(this.terpilih + 'T00:00:00');
                    d.setDate(d.getDate() + hari);
                    const baru = this.format(d);

                    if (baru >= this.batasAwal && baru <= this.batasAkhir) {
                        this.terpilih = baru;
                    }
                },

                get daftar() {
                    return this.agenda[this.terpilih] ?? [];
                },

                get modalTanggal() {
                    return this.terpilih;
                },

                get judulPanel() {
                    return this.terpilih === this.hariIni ? 'Agenda Hari Ini' : 'Agenda Tanggal Ini';
                },

                get labelTanggal() {
                    const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    const bulan = ['Januari','Februari','Maret','April','Mei','Juni',
                                   'Juli','Agustus','September','Oktober','November','Desember'];
                    const d = new Date(this.terpilih + 'T00:00:00');
                    if (isNaN(d)) return '';
                    return `${hari[d.getDay()]}, ${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
                },
            };
        }
    </script>

    @if ($errors->any())
        <script>
            document.addEventListener('alpine:initialized', () => {
                window.dispatchEvent(new CustomEvent('buka-acara'));
            });
        </script>
    @endif

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
