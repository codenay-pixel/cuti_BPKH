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
         x-show="peran === 'atasan_langsung' || peran === 'atasan'" x-cloak>
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
                        <input type="checkbox" name="hapus_tanda_tangan" value="1"
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
                       class="w-full text-sm text-gray-700 rounded-lg border-2 border-gray-300 p-2
                              file:mr-3 file:py-1.5 file:px-4 file:rounded-md file:border-0
                              file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700
                              hover:file:bg-primary-100">
                <ul class="text-xs text-gray-600 mt-2 space-y-1 list-disc list-inside">
                    <li>Format PNG atau JPG, ukuran berkas maksimal 2 MB.</li>
                    <li>Paling rapi: hasil pindai tanda tangan di kertas putih, latar dibuat
                        transparan (PNG), lebar kira-kira 3&ndash;4 kali tingginya.</li>
                    <li>Gambar akan dicetak setinggi &plusmn; 1,5 cm di atas nama pejabat.</li>
                </ul>
                @error('tanda_tangan') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
</div>
