@php $u = $user ?? null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5"
     x-data="{ peran: '{{ old('role', $u->role ?? 'pegawai') }}' }">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
            Nama Lengkap (dengan gelar) <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="name" name="name" required value="{{ old('name', $u->name ?? '') }}"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1.5">
            NIP <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="nip" name="nip" required inputmode="numeric" maxlength="18"
               value="{{ old('nip', $u->nip ?? '') }}" placeholder="199003032010012003"
               class="w-full rounded-lg border-gray-300 text-sm font-mono focus:border-primary-500 focus:ring-primary-500">
        <p class="text-xs text-gray-500 mt-1">Dipakai sebagai username saat login. Angka saja, 8&ndash;18 digit.</p>
        @error('nip') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
            Password @if (! $u) <span class="text-rose-500">*</span> @endif
        </label>
        <input type="text" id="password" name="password" {{ $u ? '' : 'required' }} minlength="8"
               placeholder="{{ $u ? 'Kosongkan bila tidak diubah' : 'Minimal 8 karakter' }}"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        @error('password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">
            Peran <span class="text-rose-500">*</span>
        </label>
        <select id="role" name="role" required x-model="peran" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            @foreach (\App\Models\User::ROLE_LABEL as $nilai => $label)
                <option value="{{ $nilai }}" @selected(old('role', $u->role ?? 'pegawai') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2 rounded-xl border-2 border-amber-200 bg-amber-50 p-4"
         x-show="peran === 'atasan_langsung'" x-cloak>
        @php $plhSaatIni = \App\Models\User::where('is_plh_kepala_balai', true)->first(); @endphp
        <label class="flex items-start gap-3">
            <input type="checkbox" id="is_plh_kepala_balai" name="is_plh_kepala_balai" value="1"
                   @checked(old('is_plh_kepala_balai', $u->is_plh_kepala_balai ?? false))
                   class="mt-1 rounded border-gray-400 text-amber-600 focus:ring-amber-500">
            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Jadikan Plh (Pelaksana Harian) Kepala Balai
                </span>
                <span class="block text-xs text-gray-600 mt-0.5">
                    Aktifkan saat Kepala Balai sedang dinas luar / tidak sempat login. Pegawai ini
                    akan bisa menyetujui atau menolak cuti di antrean Persetujuan Final, dan
                    formulir cetaknya otomatis mencantumkan jabatan
                    &ldquo;Plh. Pelaksana Harian Kepala Balai&rdquo;, bukan jabatan asalnya.
                    Hanya satu orang yang bisa aktif dalam satu waktu.
                    @if ($plhSaatIni && (! $u || $plhSaatIni->id !== $u->id))
                        <span class="block mt-1 font-medium text-amber-700">
                            Mencentang ini akan otomatis menonaktifkan Plh {{ $plhSaatIni->name }} yang sedang aktif.
                        </span>
                    @endif
                </span>
            </span>
        </label>
        @error('is_plh_kepala_balai') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="atasan_id" class="block text-sm font-medium text-gray-700 mb-1.5">Atasan Langsung</label>
        <select id="atasan_id" name="atasan_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">— Tidak ada —</option>
            @foreach ($atasanList as $atasan)
                <option value="{{ $atasan->id }}" @selected(old('atasan_id', $u->atasan_id ?? null) == $atasan->id)>
                    {{ $atasan->name }} ({{ $atasan->role_label }})
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Wajib diisi agar pengajuan cuti punya tujuan persetujuan.</p>
        @error('atasan_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1.5">Jabatan</label>
        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $u->jabatan ?? '') }}"
               placeholder="Contoh: Analis Kehutanan"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        @error('jabatan') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="unit_kerja" class="block text-sm font-medium text-gray-700 mb-1.5">Unit Kerja</label>
        <input type="text" id="unit_kerja" name="unit_kerja" value="{{ old('unit_kerja', $u->unit_kerja ?? '') }}"
               placeholder="Contoh: Seksi Pemolaan Kawasan Hutan"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        @error('unit_kerja') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tmt_pns" class="block text-sm font-medium text-gray-700 mb-1.5">TMT PNS</label>
        <input type="date" id="tmt_pns" name="tmt_pns"
               value="{{ old('tmt_pns', optional($u?->tmt_pns)->format('Y-m-d')) }}"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        <p class="text-xs text-gray-500 mt-1">Dipakai menghitung masa kerja di formulir cuti.</p>
        @error('tmt_pns') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Telepon</label>
        <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', $u->no_telp ?? '') }}"
               placeholder="0812xxxxxxxx"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        @error('no_telp') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
            Email <span class="text-gray-400 font-normal">(opsional)</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email', $u->email ?? '') }}"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        <p class="text-xs text-gray-500 mt-1">Tidak dipakai untuk login, hanya arsip data.</p>
        @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- ===== Tanda tangan pejabat =====
         Hanya relevan untuk peran yang menandatangani formulir cuti, jadi
         blok ini disembunyikan bila perannya Pegawai atau Admin. --}}
    <div class="sm:col-span-2 border-t-2 border-gray-300 pt-5"
         x-show="peran === 'atasan_langsung' || peran === 'atasan'" x-cloak
         x-data="{
            skala: {{ ((int) old('tanda_tangan_skala')) ?: ($u?->tanda_tangan_skala_aman ?? \App\Models\User::TTD_SKALA_DEFAULT) }},
            pratinjau: @js($u?->tanda_tangan_url),
            hapus: false,
            get tinggiPx() {
                return Math.round({{ \App\Models\User::TTD_TINGGI_DASAR }} * this.skala / 100 * 10) / 10;
            },
            get tinggiMm() {
                return (this.tinggiPx / 96 * 25.4).toFixed(1).replace('.', ',');
            },
            batasi() {
                let n = parseInt(this.skala);
                if (isNaN(n)) n = {{ \App\Models\User::TTD_SKALA_DEFAULT }};
                this.skala = Math.min({{ \App\Models\User::TTD_SKALA_MAX }}, Math.max({{ \App\Models\User::TTD_SKALA_MIN }}, n));
            },
            gantiBerkas(e) {
                const berkas = e.target.files[0];
                if (berkas) this.pratinjau = URL.createObjectURL(berkas);
            }
         }">
        <label for="tanda_tangan" class="block text-sm font-semibold text-gray-800 mb-1">
            Gambar Tanda Tangan
        </label>
        <p class="text-sm text-gray-600 mb-3">
            Dipakai otomatis pada Formulir Permintaan dan Pemberian Cuti yang dicetak,
            di kolom persetujuan pejabat ini.
        </p>

        <div class="flex flex-col sm:flex-row sm:items-start gap-5">
            <div class="shrink-0">
                <p class="text-xs font-medium text-gray-600 mb-1.5">Tanda tangan tersimpan</p>
                @if ($u?->tanda_tangan_url)
                    <img src="{{ $u->tanda_tangan_url }}" alt="Tanda tangan {{ $u->name }}"
                         class="h-24 w-56 object-contain bg-white border-2 border-gray-300 rounded-lg p-1">
                    <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-700">
                        <input type="checkbox" name="hapus_tanda_tangan" value="1" x-model="hapus"
                               x-on:change="pratinjau = hapus ? null : @js($u?->tanda_tangan_url)"
                               class="rounded border-gray-400 text-rose-600 focus:ring-rose-500">
                        Hapus tanda tangan ini
                    </label>
                @else
                    <div class="h-24 w-56 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500">
                        Belum ada
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <p class="text-xs font-medium text-gray-600 mb-1.5">
                    {{ $u?->tanda_tangan_url ? 'Ganti dengan gambar baru' : 'Unggah gambar' }}
                </p>
                <input type="file" id="tanda_tangan" name="tanda_tangan" accept="image/png,image/jpeg"
                       x-on:change="gantiBerkas($event)"
                       class="w-full text-sm text-gray-700 rounded-lg border-2 border-gray-300 p-2
                              file:mr-3 file:py-1.5 file:px-4 file:rounded-md file:border-0
                              file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700
                              hover:file:bg-primary-100">
                <ul class="text-xs text-gray-600 mt-2 space-y-1 list-disc list-inside">
                    <li>Format PNG atau JPG, ukuran berkas maksimal 2 MB.</li>
                    <li>Paling rapi: hasil pindai tanda tangan di kertas putih, latar dibuat
                        transparan (PNG), lebar kira-kira 3&ndash;4 kali tingginya.</li>
                    <li>Ukuran cetaknya diatur lewat penggeser di bawah, jadi gambar tidak perlu
                        dipotong ulang bila terlihat terlalu besar atau terlalu kecil.</li>
                </ul>
                @error('tanda_tangan') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ===== Penyetelan ukuran cetak =====
             Nilainya persen; 100% = tinggi 30px pada PDF (± 7,9 mm di kertas).
             Pratinjau di bawah digambar seukuran hasil cetak sungguhan. --}}
        <div class="mt-5 rounded-xl border-2 border-gray-200 bg-gray-50 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                    <label for="tanda_tangan_skala" class="block text-sm font-semibold text-gray-800">
                        Ukuran Tanda Tangan di Dokumen
                    </label>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Geser untuk memperbesar atau memperkecil gambar pada formulir cetak.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" inputmode="numeric"
                           min="{{ \App\Models\User::TTD_SKALA_MIN }}" max="{{ \App\Models\User::TTD_SKALA_MAX }}" step="5"
                           x-model.number="skala" x-on:change="batasi()"
                           class="w-20 rounded-lg border-gray-300 text-sm text-center focus:border-primary-500 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">%</span>
                    <button type="button" x-on:click="skala = {{ \App\Models\User::TTD_SKALA_DEFAULT }}"
                            class="text-xs font-medium text-primary-700 hover:text-primary-900 underline">
                        Setel ulang
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 shrink-0">Kecil</span>
                <input type="range" id="tanda_tangan_skala" name="tanda_tangan_skala"
                       min="{{ \App\Models\User::TTD_SKALA_MIN }}" max="{{ \App\Models\User::TTD_SKALA_MAX }}" step="5"
                       x-model.number="skala"
                       class="w-full h-2 accent-primary-600 cursor-pointer">
                <span class="text-xs text-gray-500 shrink-0">Besar</span>
            </div>
            <p class="text-xs text-gray-600 mt-2">
                Tinggi di kertas &plusmn; <span class="font-semibold" x-text="tinggiMm"></span> mm
                (<span x-text="tinggiPx"></span> px). Rentang yang diizinkan
                {{ \App\Models\User::TTD_SKALA_MIN }}&ndash;{{ \App\Models\User::TTD_SKALA_MAX }}%
                supaya formulir tetap muat satu halaman A4.
            </p>
            @error('tanda_tangan_skala') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror

            <p class="text-xs font-medium text-gray-600 mt-4 mb-2">Pratinjau seukuran hasil cetak</p>
            <div class="bg-white border-2 border-gray-300 rounded-lg px-4 py-3 inline-block max-w-full overflow-x-auto">
                <div class="text-center" style="font-family: 'DejaVu Sans', sans-serif; font-size: 8.5pt; line-height: 1.15; min-width: 240px;">
                    <div>{{ $u?->jabatan ?: 'Jabatan Pejabat' }},</div>
                    <template x-if="pratinjau">
                        <div class="flex items-end justify-center" x-bind:style="'height:' + tinggiPx + 'px'">
                            <img x-bind:src="pratinjau" x-bind:style="'height:' + tinggiPx + 'px'"
                                 class="object-contain" alt="Pratinjau tanda tangan">
                        </div>
                    </template>
                    <template x-if="! pratinjau">
                        <div class="flex items-center justify-center text-gray-400"
                             x-bind:style="'height:' + tinggiPx + 'px'">
                            <span style="font-size: 7pt;">(belum ada gambar)</span>
                        </div>
                    </template>
                    <div style="border-bottom: 1px dotted #000; display: inline-block; min-width: 165px;">
                        {{ $u?->name ?: 'Nama Pejabat' }}
                    </div>
                    <div>NIP. {{ $u?->nip_formatted ?: '................' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
