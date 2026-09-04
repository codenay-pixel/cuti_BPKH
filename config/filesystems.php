<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // Driver disk 'public' bisa dialihkan ke S3-compatible storage (mis.
        // Cloudflare R2) lewat env PUBLIC_DISK_DRIVER=s3, tanpa perlu ubah
        // kode di controller manapun -- semua tetap panggil Storage::disk('public').
        // Ini penting kalau di-deploy ke platform tanpa disk permanen (mis.
        // Render Free tier), karena disk lokal biasa akan hilang tiap redeploy.
        //
        // PENTING: 'root' HANYA boleh diisi untuk driver lokal. Untuk driver
        // s3, key 'root' dipakai Flysystem sebagai prefix path di dalam
        // bucket -- kalau diisi path filesystem seperti storage_path(...),
        // semua file akan ter-upload ke key yang salah/nyasar di bucket
        // (dan URL publiknya jadi 404 walau upload "berhasil").
        'public' => array_merge(
            [
                'driver' => env('PUBLIC_DISK_DRIVER', 'local'),
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ],
            env('PUBLIC_DISK_DRIVER', 'local') === 's3'
                ? [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                    'region' => env('AWS_DEFAULT_REGION', 'auto'),
                    'bucket' => env('AWS_BUCKET'),
                    'url' => env('AWS_URL'),
                    'endpoint' => env('AWS_ENDPOINT'),
                    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                ]
                : [
                    'root' => storage_path('app/public'),
                    'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
                ]
        ),

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
