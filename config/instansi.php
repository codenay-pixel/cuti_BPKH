<?php

return [

    'tagline'     => env('INSTANSI_TAGLINE', 'Sistem Manajemen Cuti Pegawai'),

    'logo_teks'   => env('INSTANSI_LOGO_TEKS', ''),

    'kementerian' => env('INSTANSI_KEMENTERIAN', 'KEMENTERIAN KEHUTANAN'),
    'direktorat'  => env('INSTANSI_DIREKTORAT', 'DIREKTORAT JENDERAL PLANOLOGI KEHUTANAN'),
    'satker'      => env('INSTANSI_SATKER', 'BALAI PEMANTAPAN KAWASAN HUTAN WILAYAH I'),

    'alamat'      => env('INSTANSI_ALAMAT', 'Jl. Pembangunan No. 6, Helvetia Timur, Kec. Medan Helvetia, Kota Medan, Sumatera Utara 20117'),
    'kontak'      => env('INSTANSI_KONTAK', 'Telepon (061) 8460485'),

    'kota'        => env('INSTANSI_KOTA', 'Medan'),

    'zona_waktu'  => match (env('APP_TIMEZONE', 'UTC')) {
        'Asia/Jakarta'  => 'WIB',
        'Asia/Makassar' => 'WITA',
        'Asia/Jayapura' => 'WIT',
        default         => '',
    },
];
