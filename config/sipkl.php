<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi modul PKL
    |--------------------------------------------------------------------------
    |
    | Semua angka default modul (batas upload, jam kerja, target bimbingan)
    | dapat diubah runtime lewat tabel `settings` oleh admin.
    | Nilai di sini hanya fallback.
    |
    */

    'upload_maks_mb' => (int) env('SIPKL_UPLOAD_MAKS_MB', 10),

    'jam_masuk' => env('SIPKL_JAM_MASUK', '08:00'),
    'jam_pulang' => env('SIPKL_JAM_PULANG', '17:00'),
    'toleransi_telat' => (int) env('SIPKL_TOLERANSI_TELAT', 15),

    'target_bimbingan' => (int) env('SIPKL_TARGET_BIMBINGAN', 8),
    'min_bimbingan_nilai' => (int) env('SIPKL_MIN_BIMBINGAN_NILAI', 4),

    'batas_jurnal_hari' => (int) env('SIPKL_BATAS_JURNAL_HARI', 3),
    'batas_absensi_persen' => (float) env('SIPKL_BATAS_ABSENSI', 80),

    'kuota_absensi' => 100,

];
