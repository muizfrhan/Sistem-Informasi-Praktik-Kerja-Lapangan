<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Umur Token Login QR
    |--------------------------------------------------------------------------
    |
    | Masa berlaku token (detik) sejak QR dibuat. Setelah habis, token tidak
    | lagi bisa dipindai maupun diklaim — statusnya menjadi `expired`.
    |
    */

    'ttl' => (int) env('QR_LOGIN_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Interval Polling
    |--------------------------------------------------------------------------
    |
    | Jarak antar pemeriksaan status (milidetik) oleh perangkat yang
    | menampilkan QR. Nilai besar = lebih hemat, tetap real-timeenough.
    |
    */

    'poll_interval' => (int) env('QR_LOGIN_POLL_INTERVAL', 2000),

    /*
    |--------------------------------------------------------------------------
    | Rate Limit
    |--------------------------------------------------------------------------
    | Batas permintaan per menit: membuat QR, mengecek status, dan mengklaim.
    */

    'throttle' => [
        'start' => env('QR_LOGIN_THROTTLE_START', 6),
        'status' => env('QR_LOGIN_THROTTLE_STATUS', 40),
        'claim' => env('QR_LOGIN_THROTTLE_CLAIM', 10),
        'decide' => env('QR_LOGIN_THROTTLE_DECIDE', 20),
    ],

];
