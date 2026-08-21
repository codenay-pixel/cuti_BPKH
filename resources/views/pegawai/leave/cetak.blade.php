@php
    /**
     * Formulir Permintaan dan Pemberian Cuti.
     * Tata letak mengikuti persis lampiran Nota Dinas Sekretaris Direktorat
     * Jenderal Planologi Kehutanan No. ND.461/SPLA/PEG/PEG.11.01/B/04/2026.
     * Formulir resmi ini TIDAK memakai kop surat.
     */
    $LT = \App\Models\LeaveType::class;

    $pemohon   = $leaveRequest->user;
    $kode      = $leaveRequest->leaveType->kode;
    $apAtasan  = $leaveRequest->approvalAtasanLangsung();
    $apPejabat = $leaveRequest->approvalKepalaBalai();

    $centang = fn (bool $ya) => $ya ? '✓' : '';

    // Masa kerja dipecah agar masuk ke empat sel kecil seperti formulir asli
    $mkTahun = $mkBulan = '';
    if ($pemohon->tmt_pns) {
        $d = $pemohon->tmt_pns->diff(now());
        $mkTahun = $d->y;
        $mkBulan = $d->m;
    }

    $saldoPerTahun = collect($saldo['rincian'])->keyBy('tahun');

    /**
     * Nama pejabat tetap dicetak walau keputusannya belum ada, supaya formulir
     * bisa dibawa untuk ditandatangani. Kotak centang di bagian VII dan VIII
     * HANYA terisi bila keputusannya sudah tercatat di sistem.
     */
    $ttdAtasan  = $penyetuju['atasan'] ?? null;
    $ttdPejabat = $penyetuju['pejabat'] ?? null;

    /**
     * Gambar tanda tangan HANYA dicetak bila keputusannya sudah tercatat di
     * sistem. Formulir yang belum diputuskan tetap keluar dengan ruang kosong
     * untuk ditandatangani manual.
     */
    $gbrAtasan  = ($apAtasan  && $ttdAtasan)  ? $ttdAtasan->tandaTanganDataUri()  : null;
    $gbrPejabat = ($apPejabat && $ttdPejabat) ? $ttdPejabat->tandaTanganDataUri() : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Formulir Permintaan dan Pemberian Cuti</title>
    <style>
        @page { margin: 8mm 13mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #000; line-height: 1.10; }

        .tempat { margin-bottom: 8px; }
        .tempat td { padding: 0; border: none; font-size: 8.5pt; }

        h1.judul { text-align: center; font-size: 9.5pt; font-weight: bold; margin: 0 0 4px; text-transform: uppercase; }

        table.bagian { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.bagian td, table.bagian th { border: 1pt solid #000000; padding: 1px 4px; vertical-align: top; }
        /* Sel kosong di kiri blok tanda tangan: garis atas dihilangkan supaya
           menyatu dengan baris kotak centang, tetapi kiri dan bawah tetap
           digambar agar kotak bagian VII/VIII tertutup rapi. */
        table.bagian td.sisi-ttd { border-top: 0; }
        td.judul-bagian { font-weight: normal; }
        .tengah { text-align: center; }
        .tebal  { font-weight: bold; }
        .coret  { text-decoration: line-through; }
        .kecil  { font-size: 7.5pt; }

        .kotak-centang { text-align: center; font-weight: bold; font-size: 10pt; }
        .isi { font-weight: bold; }

        .ttd-area { text-align: center; vertical-align: top; }
        .spasi-ttd { height: 30px; }
        .spasi-ttd-gambar { height: 38px; }
        .gambar-ttd { height: 38px; }
        .garis-titik { border-bottom: 1px dotted #000; display: inline-block; min-width: 165px; }

        .catatan { font-size: 7pt; line-height: 1.15; margin-top: 1px; }
        .catatan td { border: none; padding: 0 4px 0 0; vertical-align: top; }

        .footer-cetak { margin-top: 3px; text-align: center; font-size: 6.5pt; color: #444; }
    </style>
</head>
<body>

{{-- ===== Tempat, tanggal, dan tujuan ===== --}}
<table class="tempat" style="width:100%">
    <tr>
        <td style="width:52%">&nbsp;</td>
        <td style="width:48%">
            {{ config('instansi.kota') }}, {{ $leaveRequest->created_at->translatedFormat('j F Y') }}<br>
            Kepada Yth.<br>
            {{ $ttdPejabat?->jabatan ?? 'Pejabat yang Berwenang Memberikan Cuti' }}<br>
            di {{ config('instansi.kota') }}
        </td>
    </tr>
</table>

<h1 class="judul">Formulir Permintaan dan Pemberian Cuti</h1>

{{-- ===== I. DATA PEGAWAI ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian" colspan="8">I. DATA PEGAWAI</td></tr>
    <tr>
        <td style="width:13%">Nama</td>
        <td style="width:44%" class="isi" colspan="2">{{ $pemohon->name }}</td>
        <td style="width:12%">NIP</td>
        <td style="width:31%" class="isi" colspan="4">{{ $pemohon->nip_formatted }}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td class="isi" colspan="2">{{ $pemohon->jabatan ?? '' }}</td>
        <td>Masa Kerja</td>
        <td style="width:7%" class="tengah isi">{{ $mkTahun }}</td>
        <td style="width:9%" class="tengah">Tahun</td>
        <td style="width:6%" class="tengah isi">{{ $mkBulan }}</td>
        <td style="width:9%" class="tengah">Bulan</td>
    </tr>
    <tr>
        <td>Unit Kerja</td>
        <td class="isi" colspan="7">{{ $pemohon->unit_kerja ?? config('instansi.satker') }}</td>
    </tr>
</table>

{{-- ===== II. JENIS CUTI YANG DIAMBIL ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian" colspan="4">II. JENIS CUTI YANG DIAMBIL**</td></tr>
    <tr>
        <td style="width:36%">1. Cuti Tahunan</td>
        <td style="width:8%" class="kotak-centang">{{ $centang($kode === $LT::TAHUNAN) }}</td>
        <td style="width:41%">2. Cuti Besar</td>
        <td style="width:15%" class="kotak-centang">{{ $centang($kode === $LT::BESAR) }}</td>
    </tr>
    <tr>
        <td>3. Cuti Sakit</td>
        <td class="kotak-centang">{{ $centang($kode === $LT::SAKIT) }}</td>
        <td>4. Cuti Melahirkan</td>
        <td class="kotak-centang">{{ $centang($kode === $LT::MELAHIRKAN) }}</td>
    </tr>
    <tr>
        <td>5. Cuti Karena Alasan Penting</td>
        <td class="kotak-centang">{{ $centang($kode === $LT::ALASAN_PENTING) }}</td>
        <td>6. Cuti di Luar Tanggungan Negara</td>
        <td class="kotak-centang">{{ $centang($kode === $LT::DILUAR_TANGGUNGAN) }}</td>
    </tr>
</table>

{{-- ===== III. ALASAN CUTI ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian">III. ALASAN CUTI</td></tr>
    <tr><td class="isi" style="height:22px">{{ $leaveRequest->alasan }}</td></tr>
</table>

{{-- ===== IV. LAMANYA CUTI ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian" colspan="6">IV. LAMANYA CUTI</td></tr>
    <tr>
        <td style="width:11%" class="tengah">Selama</td>
        <td style="width:7%" class="tengah isi">{{ $leaveRequest->jumlah_hari }}</td>
        <td style="width:31%">
            (hari<span class="coret">/bulan/tahun</span>)*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tanggal
        </td>
        <td style="width:22%" class="tengah isi">{{ $leaveRequest->tanggal_mulai->translatedFormat('j F Y') }}</td>
        <td style="width:6%" class="tengah">s/d</td>
        <td style="width:23%" class="tengah isi">{{ $leaveRequest->tanggal_selesai->translatedFormat('j F Y') }}</td>
    </tr>
</table>

{{-- ===== V. CATATAN CUTI ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian" colspan="5">V. CATATAN CUTI***</td></tr>
    <tr>
        <td colspan="3">1. CUTI TAHUNAN</td>
        <td style="width:41%">2. CUTI BESAR</td>
        <td style="width:9%">&nbsp;</td>
    </tr>
    <tr>
        <td style="width:12%" class="tengah">Tahun</td>
        <td style="width:11%" class="tengah">Sisa</td>
        <td style="width:27%" class="tengah">Keterangan</td>
        <td>3. CUTI SAKIT</td>
        <td>&nbsp;</td>
    </tr>
    @foreach ([2, 1, 0] as $i)
        @php
            $th = $saldo['tahun'] - $i;
            $baris = $saldoPerTahun[$th] ?? null;
            $kananLabel = [
                2 => '4. CUTI MELAHIRKAN',
                1 => '5. CUTI KARENA ALASAN PENTING',
                0 => '6. CUTI DI LUAR TANGGUNGAN NEGARA',
            ][$i];
        @endphp
        <tr>
            <td class="tengah tebal">{{ $th }}</td>
            <td class="tengah isi">{{ $baris['sisa'] ?? '' }}</td>
            <td class="kecil">{{ $baris['catatan'] ?? '' }}</td>
            <td>{{ $kananLabel }}</td>
            <td>&nbsp;</td>
        </tr>
    @endforeach
</table>

{{-- ===== VI. ALAMAT SELAMA MENJALANKAN CUTI ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian" colspan="3">VI. ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
    <tr>
        <td style="width:57%">&nbsp;</td>
        <td style="width:12%">TELP</td>
        <td style="width:31%" class="isi">{{ $leaveRequest->telepon_cuti ?? $pemohon->no_telp }}</td>
    </tr>
    <tr>
        <td class="isi" style="height:56px">{{ $leaveRequest->alamat_cuti }}</td>
        <td class="ttd-area" colspan="2">
            Hormat saya,
            <div class="spasi-ttd"></div>
            <span class="garis-titik isi">{{ $pemohon->name }}</span><br>
            NIP. {{ $pemohon->nip_formatted }}
        </td>
    </tr>
</table>

{{-- ===== VII. PERTIMBANGAN ATASAN LANGSUNG ===== --}}
<table class="bagian">
    <tr><td class="judul-bagian" colspan="4">VII. PERTIMBANGAN ATASAN LANGSUNG**</td></tr>
    <tr class="tengah">
        <td style="width:19%">DISETUJUI</td>
        <td style="width:22%">PERUBAHAN****</td>
        <td style="width:24%">DITANGGUHKAN****</td>
        <td style="width:35%">TIDAK DISETUJUI****</td>
    </tr>
    <tr>
        <td class="kotak-centang">{{ $centang($apAtasan?->keputusan === 'disetujui') }}</td>
        <td class="kotak-centang">&nbsp;</td>
        <td class="kotak-centang">&nbsp;</td>
        <td class="kotak-centang">{{ $centang($apAtasan?->keputusan === 'ditolak') }}</td>
    </tr>
    <tr>
        <td colspan="3" class="sisi-ttd kecil" style="vertical-align:bottom; height:26px">
            @if ($apAtasan?->catatan)
                Catatan: {{ $apAtasan->catatan }}
            @endif
        </td>
        <td class="ttd-area">
            @if ($ttdAtasan)
                {{ $ttdAtasan->jabatan }}
                @if ($gbrAtasan)
                    <div class="spasi-ttd-gambar"><img src="{{ $gbrAtasan }}" class="gambar-ttd" alt=""></div>
                @else
                    <div class="spasi-ttd"></div>
                @endif
                <span class="garis-titik isi">{{ $ttdAtasan->name }}</span><br>
                NIP. {{ $ttdAtasan->nip_formatted }}
            @else
                <div class="spasi-ttd"></div>
                <span class="kecil">Tidak berlaku &mdash; pemohon tidak memiliki atasan langsung.</span>
            @endif
        </td>
    </tr>
</table>

{{-- ===== VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI ===== --}}
<table class="bagian" style="margin-bottom:0">
    <tr><td class="judul-bagian" colspan="4">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td></tr>
    <tr class="tengah">
        <td style="width:19%">DISETUJUI</td>
        <td style="width:22%">PERUBAHAN****</td>
        <td style="width:24%">DITANGGUHKAN****</td>
        <td style="width:35%">TIDAK DISETUJUI****</td>
    </tr>
    <tr>
        <td class="kotak-centang">{{ $centang($apPejabat?->keputusan === 'disetujui') }}</td>
        <td class="kotak-centang">&nbsp;</td>
        <td class="kotak-centang">&nbsp;</td>
        <td class="kotak-centang">{{ $centang($apPejabat?->keputusan === 'ditolak') }}</td>
    </tr>
    <tr>
        <td colspan="3" class="sisi-ttd kecil" style="vertical-align:bottom; height:26px">
            @if ($apPejabat?->catatan)
                Catatan: {{ $apPejabat->catatan }}
            @endif
        </td>
        <td class="ttd-area">
            {{ $ttdPejabat?->jabatan ?? 'Pejabat yang Memberikan Cuti' }}
            @if ($gbrPejabat)
                <div class="spasi-ttd-gambar"><img src="{{ $gbrPejabat }}" class="gambar-ttd" alt=""></div>
            @else
                <div class="spasi-ttd"></div>
            @endif
            <span class="garis-titik isi">{{ $ttdPejabat?->name }}</span><br>
            NIP. {{ $ttdPejabat?->nip_formatted }}
        </td>
    </tr>
</table>

{{-- ===== Catatan kaki ===== --}}
<table class="catatan">
    <tr><td colspan="2">Catatan :</td></tr>
    <tr><td style="width:34px">*</td><td>Coret yang tidak perlu</td></tr>
    <tr><td>**</td><td>Pilih salah satu dengan member tanda centang (√)</td></tr>
    <tr><td>***</td><td>diisi oleh pejabat yang menangani bidang Kepegawaian sebelum PNS mengajukan cuti</td></tr>
    <tr><td>****</td><td>diberi tanda centang (√) dan alasannya</td></tr>
</table>

<div class="footer-cetak">
    Dicetak dari {{ config('app.name') }} pada {{ now()->translatedFormat('j F Y, H:i') }} {{ config('instansi.zona_waktu') }}.
    Dokumen ini memerlukan tanda tangan pejabat berwenang untuk berlaku.
</div>

</body>
</html>
