<?php

/*
|--------------------------------------------------------------------------
| Identitas Aplikasi & Instansi
|--------------------------------------------------------------------------
| SATU-SATUNYA tempat nama aplikasi dan identitas instansi ditulis.
| Semua halaman (navigasi, login, footer, kop surat PDF) membaca dari sini,
| jadi cukup ubah nilai di .env — tidak perlu menyentuh kode.
|
| Nama aplikasi sendiri diambil dari APP_NAME di .env.
*/

return [
    // Ditampilkan di bawah nama aplikasi pada navigasi & halaman login
    'tagline'     => env('INSTANSI_TAGLINE', 'Sistem Manajemen Cuti Pegawai'),

    // Huruf pada kotak logo. Dikosongkan = diambil 2 huruf pertama APP_NAME.
    'logo_teks'   => env('INSTANSI_LOGO_TEKS', ''),

    // Baris kop surat, dari atas ke bawah
    'kementerian' => env('INSTANSI_KEMENTERIAN', 'KEMENTERIAN KEHUTANAN'),
    'direktorat'  => env('INSTANSI_DIREKTORAT', 'DIREKTORAT JENDERAL PLANOLOGI KEHUTANAN'),
    'satker'      => env('INSTANSI_SATKER', 'BALAI PEMANTAPAN KAWASAN HUTAN WILAYAH I'),

    // Dikosongkan = baris tidak dicetak di kop surat
    'alamat'      => env('INSTANSI_ALAMAT', 'Jl. Pembangunan No. 6, Helvetia Timur, Kec. Medan Helvetia, Kota Medan, Sumatera Utara 20117'),
    'kontak'      => env('INSTANSI_KONTAK', 'Telepon (061) 8460485'),

    // Dipakai pada "Medan, 19 Agustus 2026" dan "di Medan" pada formulir
    'kota'        => env('INSTANSI_KOTA', 'Medan'),

    // Singkatan zona waktu untuk keterangan jam. Mengikuti APP_TIMEZONE,
    // jadi tidak perlu diubah manual bila satker berada di zona lain.
    'zona_waktu'  => match (env('APP_TIMEZONE', 'UTC')) {
        'Asia/Jakarta'  => 'WIB',
        'Asia/Makassar' => 'WITA',
        'Asia/Jayapura' => 'WIT',
        default         => '',
    },
];
