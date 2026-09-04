@php
    /**
     * Formulir bersama untuk Ajukan Cuti dan Ubah Pengajuan.
     * $leaveRequest berisi pengajuan yang sedang diubah, atau null saat membuat baru.
     */
    $lr   = $leaveRequest ?? null;
    $ubah = $lr !== null;

    $meta = $leaveTypes->mapWithKeys(fn ($t) => [$t->id => [
        'nama'          => $t->nama_cuti,
        'perluLampiran' => (bool) $t->perlu_lampiran,
        'kurangiSaldo'  => (bool) $t->mengurangi_saldo,
        'maksHari'      => $t->maks_hari,
        'dasarHukum'    => $t->dasar_hukum,
        'syarat'        => $t->syaratList(),
    ]]);
@endphp

    <div class="pb-12" x-data="formCuti()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                    <p class="font-medium mb-1">{{ $ubah ? 'Perubahan belum dapat disimpan:' : 'Pengajuan belum dapat diproses:' }}</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" enctype="multipart/form-data"
                  action="{{ $ubah ? route('leave.update', $lr) : route('leave.store') }}"
                  class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @csrf
                @if ($ubah) @method('PUT') @endif

                <div class="lg:col-span-2 space-y-6">

                    <div class="bg-white border border-gray-300 rounded-xl p-6 space-y-5">
                        <h3 class="font-semibold text-sm text-gray-800 pb-3 border-b border-gray-300">
                            I. Jenis dan Waktu Cuti
                        </h3>

                        <div>
                            <label for="leave_type_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Jenis Cuti <span class="text-rose-500">*</span>
                            </label>
                            <select id="leave_type_id" name="leave_type_id" x-model="jenisId" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">— Pilih salah satu dari 6 jenis cuti —</option>
                                @foreach ($leaveTypes as $index => $type)
                                    <option value="{{ $type->id }}" @selected(old('leave_type_id', $lr?->leave_type_id) == $type->id)>
                                        {{ $index + 1 }}. {{ $type->nama_cuti }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tanggal Mulai <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="tanggal_mulai" name="tanggal_mulai" x-model="mulai" required
                                       value="{{ old('tanggal_mulai', $lr?->tanggal_mulai?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('tanggal_mulai') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tanggal Selesai <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="tanggal_selesai" name="tanggal_selesai" x-model="selesai" required
                                       value="{{ old('tanggal_selesai', $lr?->tanggal_selesai?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('tanggal_selesai') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lama Cuti</label>
                                <div class="h-[38px] flex items-center px-3 rounded-lg bg-gray-100 border border-gray-300 text-sm font-semibold text-gray-800">
                                    <span x-text="jumlahHari"></span>
                                    <span class="ms-1 font-normal text-gray-500">hari kerja</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500">
                            Sabtu, Minggu, dan tanggal di luar hari kerja tidak dihitung sebagai hari cuti.
                        </p>

                        <template x-if="jenis && jenis.maksHari && jumlahHari > jenis.maksHari">
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
                                <span x-text="jenis.nama"></span> maksimal <span x-text="jenis.maksHari"></span> hari.
                            </div>
                        </template>

                        <template x-if="jenis && jenis.kurangiSaldo && jumlahHari > {{ max(0, $saldo['total_tersedia'] - $tertahan) }}">
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                Jumlah hari melebihi saldo cuti tahunan yang tersedia
                                ({{ max(0, $saldo['total_tersedia'] - $tertahan) }} hari). Pengajuan akan ditolak sistem.
                            </div>
                        </template>
                    </div>

                    <div class="bg-white border border-gray-300 rounded-xl p-6 space-y-5">
                        <h3 class="font-semibold text-sm text-gray-800 pb-3 border-b border-gray-300">
                            II. Alasan dan Alamat Selama Cuti
                        </h3>

                        <div>
                            <label for="alasan" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Alasan Cuti <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="alasan" name="alasan" rows="3" required maxlength="1000"
                                      placeholder="Contoh: Keperluan keluarga, mengantar orang tua berobat ke Medan."
                                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('alasan', $lr?->alasan) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter. Kalimat ini akan tercetak di formulir resmi.</p>
                            @error('alasan') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="alamat_cuti" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Alamat Selama Menjalankan Cuti <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="alamat_cuti" name="alamat_cuti" required maxlength="255"
                                       value="{{ old('alamat_cuti', $lr?->alamat_cuti) }}" placeholder="Contoh: Jl. Merdeka No. 10, Padangsidimpuan"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('alamat_cuti') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="telepon_cuti" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Telepon yang Dapat Dihubungi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="telepon_cuti" name="telepon_cuti" required maxlength="30"
                                       value="{{ old('telepon_cuti', $lr?->telepon_cuti ?? auth()->user()->no_telp) }}" placeholder="0812xxxxxxxx"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('telepon_cuti') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-300 rounded-xl p-6 space-y-4">
                        <h3 class="font-semibold text-sm text-gray-800 pb-3 border-b border-gray-300">
                            III. Dokumen Pendukung
                        </h3>

                        <div>
                            <label for="lampiran" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Unggah Berkas
                                <template x-if="jenis && jenis.perluLampiran">
                                    <span class="text-rose-500">*</span>
                                </template>
                                <template x-if="jenis && !jenis.perluLampiran">
                                    <span class="text-gray-400 font-normal">(opsional)</span>
                                </template>
                            </label>
                            <input type="file" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg p-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            <p class="text-xs text-gray-500 mt-1">Format PDF, JPG, atau PNG. Maksimal 2 MB per berkas.</p>
                            @error('lampiran') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror

                            @if ($ubah && $lr->lampiran)
                                <div class="mt-3 flex items-center justify-between gap-3 p-3 rounded-lg bg-gray-50 border border-gray-300">
                                    <div class="min-w-0 text-xs text-gray-600">
                                        <p class="font-medium text-gray-800">Berkas saat ini</p>
                                        <a href="{{ $lr->lampiran_url }}" target="_blank"
                                           class="text-primary-600 hover:underline">Lihat berkas yang sudah diunggah</a>
                                    </div>
                                    <span class="shrink-0 text-[11px] text-gray-400 text-right">
                                        Biarkan kosong<br>bila tidak diganti
                                    </span>
                                </div>
                            @endif
                        </div>

                        <template x-if="jenis && jenis.perluLampiran">
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                <span x-text="jenis.nama"></span> <strong>wajib</strong> menyertakan dokumen pendukung.
                                Bila berkas lebih dari satu, gabungkan dulu menjadi satu file PDF.
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="bg-primary-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-primary-700 transition">
                            {{ $ubah ? 'Simpan Perubahan' : 'Kirim Pengajuan' }}
                        </button>
                        <a href="{{ $ubah ? route('leave.show', $lr) : route('leave.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-800">Batal</a>
                    </div>
                </div>

                <div class="space-y-6">
                    <x-saldo-cuti :saldo="$saldo" :tertahan="$tertahan" />

                    <div class="bg-white border border-gray-300 rounded-xl overflow-hidden">
                        <div class="px-5 py-3 bg-gray-800 text-white">
                            <h3 class="font-semibold text-sm">Syarat Dokumen</h3>
                            <p class="text-[11px] text-gray-300" x-text="jenis ? jenis.nama : 'Pilih jenis cuti dahulu'"></p>
                        </div>

                        <template x-if="!jenis">
                            <p class="px-5 py-6 text-sm text-gray-500 text-center">
                                Pilih jenis cuti untuk melihat dokumen apa saja yang perlu dilampirkan.
                            </p>
                        </template>

                        <template x-if="jenis">
                            <div class="px-5 py-4">
                                <template x-if="jenis.syarat.length === 0">
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Tidak ada syarat dokumen khusus untuk jenis cuti ini.
                                        Anda tetap boleh melampirkan berkas pendukung bila diperlukan.
                                    </p>
                                </template>

                                <ul class="space-y-2">
                                    <template x-for="(item, i) in jenis.syarat" :key="i">
                                        <li class="flex gap-2 text-xs text-gray-700 leading-relaxed">
                                            <span class="text-primary-600 mt-0.5">&#9679;</span>
                                            <span x-text="item"></span>
                                        </li>
                                    </template>
                                </ul>
                                <p class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-400"
                                   x-text="jenis.dasarHukum ? 'Dasar hukum: ' + jenis.dasarHukum : ''"></p>
                            </div>
                        </template>
                    </div>

                    <div class="bg-white border border-gray-300 rounded-xl p-5">
                        @php
                            $sayaKepalaBalai = auth()->user()->isKepalaBalai() && ! auth()->user()->atasan_id;

                            $langkah = $sayaKepalaBalai
                                ? [
                                    'Anda mengirim pengajuan',
                                    'Anda menyetujui sendiri lewat menu Persetujuan Final',
                                    'Surat cuti dapat dicetak',
                                  ]
                                : [
                                    'Anda mengirim pengajuan',
                                    'Atasan Langsung',
                                    'Pejabat Pemberi Cuti (Kepala Balai)',
                                    'Surat cuti dapat dicetak',
                                  ];
                        @endphp

                        <h3 class="font-semibold text-sm text-gray-800 mb-3">Alur Persetujuan</h3>

                        <ol class="space-y-3 text-xs text-gray-600">
                            @foreach ($langkah as $i => $teks)
                                <li class="flex gap-2">
                                    <span class="shrink-0 w-5 h-5 rounded-full text-white flex items-center justify-center text-[10px]
                                                 {{ $i === 0 ? 'bg-primary-600' : 'bg-gray-300' }}">{{ $i + 1 }}</span>
                                    <span>
                                        {{ $teks }}
                                        @if (! $sayaKepalaBalai && $i === 1)
                                            <strong class="block text-gray-800">
                                                {{ auth()->user()->atasan->name ?? 'belum diatur — hubungi admin' }}
                                            </strong>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ol>

                        @if ($sayaKepalaBalai)
                            <p class="mt-3 pt-3 border-t border-gray-100 text-[11px] text-gray-500 leading-relaxed">
                                Sebagai Kepala Balai Anda berada di puncak rantai persetujuan, sehingga
                                pengajuan ini langsung masuk ke menu Persetujuan Final milik Anda.
                            </p>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formCuti() {
            return {
                meta: @json($meta),
                jenisId: @json((string) old('leave_type_id', $lr?->leave_type_id ?? '')),
                mulai: @json(old('tanggal_mulai', $lr?->tanggal_mulai?->format('Y-m-d') ?? '')),
                selesai: @json(old('tanggal_selesai', $lr?->tanggal_selesai?->format('Y-m-d') ?? '')),

                get jenis() {
                    return this.jenisId ? (this.meta[this.jenisId] ?? null) : null;
                },

                /** Hitung hari kerja (Senin–Jumat) di antara dua tanggal. */
                get jumlahHari() {
                    if (!this.mulai || !this.selesai) return 0;

                    const a = new Date(this.mulai + 'T00:00:00');
                    const b = new Date(this.selesai + 'T00:00:00');
                    if (isNaN(a) || isNaN(b) || b < a) return 0;

                    let total = 0;
                    const kursor = new Date(a);
                    while (kursor <= b) {
                        const hari = kursor.getDay();
                        if (hari !== 0 && hari !== 6) total++;
                        kursor.setDate(kursor.getDate() + 1);
                    }
                    return total;
                },
            };
        }
    </script>
