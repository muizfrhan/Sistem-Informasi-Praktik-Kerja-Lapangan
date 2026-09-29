<?php

// Verifikasi manual encoder QR pada SertifikatService.
// Jalankan: php tests/Manual/test_qr.php

require 'C:/xampp/htdocs/SIPKL/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/SIPKL/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = new App\Services\SertifikatService(new App\Services\FileStorageService());
$teks = 'https://sipkl.test/certificate/verify/SIPKL-2026-0001';
$matrix = $svc->qrMatrix($teks, 2);
$size = count($matrix);

echo "Teks   : {$teks}\n";
echo "Ukuran : {$size} x {$size}\n\n";

$blok = $matrix[0][0] ? '##' : '..';
for ($r = 0; $r < $size; $r++) {
    $line = '';
    for ($c = 0; $c < $size; $c++) {
        $line .= $matrix[$r][$c] ? '##' : '  ';
    }
    echo $line . "\n";
}

$finder = function (array $m, int $r0, int $c0): bool {
    for ($r = 0; $r < 7; $r++) {
        for ($c = 0; $c < 7; $c++) {
            $on = ($r === 0 || $r === 6 || $c === 0 || $c === 6)
                || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
            if ($m[$r0 + $r][$c0 + $c] !== $on) {
                return false;
            }
        }
    }

    return true;
};

$hasil = [
    'Finder kiri-atas' => $finder($matrix, 0, 0),
    'Finder kanan-atas' => $finder($matrix, 0, $size - 7),
    'Finder kiri-bawah' => $finder($matrix, $size - 7, 0),
    'Timing row 6' => ($matrix[6][8] === true && $matrix[6][9] === false),
    'Dark module' => (bool) $matrix[$size - 8][8],
];

echo "\n--- validasi struktur QR ---\n";
$gagal = 0;
foreach ($hasil as $nama => $ok) {
    echo str_pad($nama, 20) . ': ' . ($ok ? 'OK' : 'GAGAL') . "\n";
    $gagal += $ok ? 0 : 1;
}

echo "\nTotal gagal: {$gagal}\n";
exit($gagal > 0 ? 1 : 0);
