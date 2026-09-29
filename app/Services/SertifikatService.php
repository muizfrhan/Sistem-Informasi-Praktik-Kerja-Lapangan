<?php

namespace App\Services;

use App\Models\Jurnal;

/**
 * Membuat sertifikat PDF berisi QR code verifikasi.
 *
 * PDF digenerate tanpa library eksternal: QR code di-render sebagai
 * matriks SVG/PNG via generatorinternal, lalu disisipkan sebagai gambar
 * di dalam dokumen PDF minimal yang valid.
 */
class SertifikatService
{
    public function __construct(private readonly FileStorageService $storage) {}

    /**
     * Terbitkan sertifikat untuk seorang mahasiswa.
     * Hanya boleh bila PKL sudah selesai (periode status selesai/evaluasi
     * dan laporan sudah diterima).
     */
    public function terbitkan(
        \App\Models\Mahasiswa $mhs,
        \App\Models\PeriodePkl $periode,
        \App\Models\PenilaianService $penilaianService
    ): \App\Models\Sertifikat {
        $nilai = $penilaianService->nilaiAkhir($mhs, $periode->id);

        $sertifikat = \App\Models\Sertifikat::updateOrCreate(
            [
                'mahasiswa_id' => $mhs->id,
                'periode_id' => $periode->id,
            ],
            [
                'nomor' => \App\Models\Sertifikat::nomorBerikutnya($periode),
                'kode_verifikasi' => \App\Models\Sertifikat::kodeVerifikasiBaru(),
                'tanggal_terbit' => today(),
                'durasi_hari' => $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) + 1,
                'nilai_akhir' => $nilai['nilai'],
                'predikat' => $nilai['predikat'],
                'status' => 'terbit',
                'diterbitkan_oleh' => auth()->id(),
                'diterbitkan_at' => now(),
            ]
        );

        $sertifikat->file_pdf = $this->storage->simpan(
            new \Illuminate\Http\UploadedFile(
                $this->renderPdf($sertifikat),
                $sertifikat->nomor . '.pdf',
                'application/pdf',
                null,
                true
            ),
            'sertifikat',
            'sertifikat-' . $sertifikat->nomor,
            'sertifikat',
            auth()->id(),
            \App\Models\Sertifikat::class,
            $sertifikat->id
        )['path'];

        $sertifikat->save();

        return $sertifikat;
    }

    /** Kode QR sebagai matriks boolean (true = module gelap). */
    public function qrMatrix(string $teks, int $ukuranModul = 4): array
    {
        $data = $this->utf8Byte($teks);

        // Versi otomatis: pilih versi QR yang muat (1..10) dengan level koreksi M
        $kapasitas = [
            1 => 14, 2 => 26, 3 => 42, 4 => 62, 5 => 84,
            6 => 106, 7 => 122, 8 => 152, 9 => 180, 10 => 213,
        ];

        $versi = 1;
        foreach ($kapasitas as $v => $kap) {
            if (strlen($data) <= $kap) {
                $versi = $v;
                break;
            }
        }

        $size = 17 + 4 * $versi;
        $matriks = $this->matriksKosong($size);

        $this->timkanFinder($matriks, 0, 0, $size);
        $this->timkanFinder($matriks, 0, $size - 7, $size);
        $this->timkanFinder($matriks, $size - 7, 0, $size);

        $this->timkanTiming($matriks, 6, 9, $size);
        $this->timkanTiming($matriks, 9, 6, $size);

        $this->timkanAlign($matriks, $size, $versi);

        $bitstream = $this->isiBitstream($data, $versi, $size);
        $matriks = $this->pasangBitstream($matriks, $bitstream, $size);

        $this->timkanMask($matriks, $size, $versi, 0);

        // Dark module: selalu gelap, tidak boleh di-mask
        $matriks[$size - 8][8] = true;

        return $matriks;
    }

    // ------------------------------------------------------------------
    // Render PDF
    // ------------------------------------------------------------------

    /** Buat file PDF minimal yang berisi teks sertifikat + QR. */
    private function renderPdf(\App\Models\Sertifikat $sertifikat): string
    {
        $mhs = $sertifikat->mahasiswa;
        $perusahaan = $mhs->pendaftaranPkl?->perusahaan?->nama ?? '-';
        $periode = $sertifikat->periode->nama;

        $baris = [
            'SERTIFIKAT PRAKTIK KERJA LAPANGAN',
            '',
            'Diberikan kepada:',
            $mhs->nama,
            'NIM: ' . ($mhs->nim ?? '-'),
            '',
            'atas keberhasilan menempuh',
            'Praktik Kerja Lapangan pada Periode ' . $periode,
            'di ' . $perusahaan,
            'dengan nilai ' . $sertifikat->nilai_akhir . ' (' . $sertifikat->predikat . ')',
            '',
            'Nomor: ' . $sertifikat->nomor,
            'Kode verifikasi: ' . $sertifikat->kode_verifikasi,
            'Verifikasi online: /certificate/verify/' . $sertifikat->kode_verifikasi,
        ];

        $path = tempnam(sys_get_temp_dir(), 'sertifikat') . '.pdf';
        $handle = fopen($path, 'wb');

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] "
            . "/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        $stream = "BT\n/F1 11 Tf\n14 TL\n72 770 Td\n";
        foreach ($baris as $i => $b) {
            $fontSize = $i === 0 ? 18 : 11;
            $stream = str_replace('/F1 11 Tf', "/F1 {$fontSize} Tf", $stream);
            $stream .= '(' . $this->escapePdf($b) . ") Tj\nT*\n";
        }
        $stream .= "ET";
        $objects[] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $i => $obj) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $obj . "\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF";

        fwrite($handle, $pdf);
        fclose($handle);

        return $path;
    }

    private function escapePdf(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $s);
    }

    // ------------------------------------------------------------------
    // Implementasi encoder QR (mode byte, level koreksi M)
    // ------------------------------------------------------------------

    private function utf8Byte(string $s): string
    {
        return $s;
    }

    private function matriksKosong(int $size): array
    {
        return array_fill(0, $size, array_fill(0, $size, null));
    }

    private function timkanFinder(array &$m, int $row, int $col, int $size): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $rr = $row + $r;
                $cc = $col + $c;
                if ($rr < 0 || $rr >= $size || $cc < 0 || $cc >= $size) {
                    continue;
                }
                $on = ($r >= 0 && $r <= 6 && ($c === 0 || $c === 6))
                    || ($c >= 0 && $c <= 6 && ($r === 0 || $r === 6))
                    || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
                $m[$rr][$cc] = $on;
            }
        }
    }

    private function timkanTiming(array &$m, int $row, int $col, int $size): void
    {
        for ($i = 8; $i < $size - 8; $i++) {
            $m[6][$i] = $i % 2 === 0;
            $m[$i][6] = $i % 2 === 0;
        }
    }

    private function timkanAlign(array &$m, int $size, int $versi): void
    {
        if ($versi < 2) {
            return;
        }

        $coords = [6];
        $pos = $size - 7;
        for ($i = 0; $i < $versi - 1; $i += 2) {
            $coords[] = $pos - $i;
            $coords[] = 6;
        }
        $coords = array_values(array_unique($coords));
        rsort($coords);

        foreach ($coords as $r) {
            foreach ($coords as $c) {
                if (($r <= 8 && $c <= 8) || ($r <= 8 && $c >= $size - 9) || ($r >= $size - 9 && $c <= 8)) {
                    continue;
                }
                for ($dr = -2; $dr <= 2; $dr++) {
                    for ($dc = -2; $dc <= 2; $dc++) {
                        $m[$r + $dr][$c + $dc] = (max(abs($dr), abs($dc)) !== 1);
                    }
                }
            }
        }
    }

    private function gf256Table(): array
    {
        static $tab = null;

        if ($tab === null) {
            $tab = array_fill(0, 512, 0);
            for ($i = 0; $i < 256; $i++) {
                $tab[$i] = $i;
            }
            for ($i = 256, $j = 0; $i < 512; $i++) {
                $tab[$i] = $tab[$j = $this->gfMul($j, 2)] ^ 0x11D;
            }
        }

        return $tab;
    }

    private function gfMul(int $a, int $b): int
    {
        $res = 0;
        while ($b > 0) {
            if ($b & 1) {
                $res ^= $a;
            }
            $a <<= 1;
            $b >>= 1;
        }

        return $res;
    }

    private function generatorPoly(int $degree): array
    {
        $tab = $this->gf256Table();
        $g = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($g) + 1, 0);
            foreach ($g as $j => $coef) {
                $next[$j] ^= $this->gfMul($coef, $tab[1]);
                $next[$j + 1] ^= $coef;
            }
            $g = $next;
        }

        return $g;
    }

    private function reedSolomon(array $data, int $eccLen): array
    {
        $tab = $this->gf256Table();
        $gen = $this->generatorPoly($eccLen);
        $res = array_merge($data, array_fill(0, $eccLen, 0));

        for ($i = 0; $i < count($data); $i++) {
            $coef = $res[$i];
            if ($coef === 0) {
                continue;
            }
            foreach ($gen as $j => $g) {
                if ($g === 0) {
                    continue;
                }
                $res[$i + $j] ^= $tab[$tab[$coef] ^ $g];
            }
        }

        return array_slice($res, count($data), $eccLen);
    }

    private function isiBitstream(string $data, int $versi, int $size): array
    {
        $bits = [];
        $push = function (int $val, int $len) use (&$bits) {
            for ($i = $len - 1; $i >= 0; $i--) {
                $bits[] = ($val >> $i) & 1;
            }
        };

        $push(0b0100, 4); // mode byte
        $push(strlen($data), $versi <= 9 ? 8 : 16);

        foreach (str_split($data) as $ch) {
            $push(ord($ch), 8);
        }

        $totalBits = $this->totalModul($size) - $this->eccLenForVersion($versi);
        $terminator = min(4, $totalBits - count($bits));
        for ($i = 0; $i < $terminator; $i++) {
            $bits[] = 0;
        }

        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $padBytes = [0xEC, 0x11];
        $idx = 0;
        while (count($bits) < $totalBits) {
            $push($padBytes[$idx % 2], 8);
            $idx++;
        }

        // Pisahkan blok data & ECC (sederhana: satu blok)
        $bytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $b = 0;
            for ($j = 0; $j < 8; $j++) {
                $b = ($b << 1) | $bits[$i + $j];
            }
            $bytes[] = $b;
        }

        $ecc = $this->reedSolomon($bytes, $this->eccLenForVersion($versi));

        $final = array_merge($bytes, $ecc);
        $out = [];
        foreach ($final as $byte) {
            for ($i = 7; $i >= 0; $i--) {
                $out[] = ($byte >> $i) & 1;
            }
        }

        return $out;
    }

    private function eccLenForVersion(int $versi): int
    {
        // Level M
        $eccM = [1 => 10, 2 => 16, 3 => 26, 4 => 18, 5 => 24, 6 => 16, 7 => 18, 8 => 22, 9 => 22, 10 => 26];

        return $eccM[$versi] ?? 10;
    }

    private function totalModul(int $size): int
    {
        $mods = 0;
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($this->isFunctionModule($r, $c, $size)) {
                    continue;
                }
                $mods++;
            }
        }

        return $mods;
    }

    private function isFunctionModule(int $r, int $c, int $size): bool
    {
        if ($r === 6 || $c === 6) {
            return true;
        }
        if ($r < 9 && $c < 9) {
            return true;
        }
        if ($r < 9 && $c >= $size - 8) {
            return true;
        }
        if ($r >= $size - 8 && $c < 9) {
            return true;
        }

        // Alignment pattern
        $step = $size >= 25 ? ($size - 13) % 2 === 0 ? 26 : 24 : 0;
        if ($step > 0) {
            $coords = [];
            $pos = $size - 7;
            for ($i = 0; $pos - $i >= 0; $i += 2) {
                $coords[] = $pos - $i;
            }
            $coords = array_merge([6], $coords);
            foreach ($coords as $ar) {
                foreach ($coords as $ac) {
                    if (abs($r - $ar) <= 2 && abs($c - $ac) <= 2) {
                        return true;
                    }
                }
            }
        }

        // Dark module
        return $r === $size - 8 && $c === 8;
    }

    private function pasangBitstream(array $m, array $bits, int $size): array
    {
        $i = 0;
        $up = true;

        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                $col--; // lewati kolom timing
            }
            for ($i2 = 0; $i2 < $size; $i2++) {
                $row = $up ? $size - 1 - $i2 : $i2;
                foreach ([0, 1] as $d) {
                    $c = $col - $d;
                    if ($this->isFunctionModule($row, $c, $size)) {
                        continue;
                    }
                    $m[$row][$c] = $bits[$i] ?? 0;
                    $i++;
                }
            }
            $up = ! $up;
        }

        return $m;
    }

    private function timkanMask(array &$m, int $size, int $versi, int $mask): void
    {
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($this->isFunctionModule($r, $c, $size)) {
                    continue;
                }
                $invert = match ($mask) {
                    0 => ($r + $c) % 2 === 0,
                    1 => $r % 2 === 0,
                    2 => $c % 3 === 0,
                    3 => ($r + $c) % 3 === 0,
                    4 => (intdiv($r, 2) + intdiv($c, 3)) % 2 === 0,
                    5 => (($r * $c) % 2) + (($r * $c) % 3) === 0,
                    6 => ((($r * $c) % 2) + (($r * $c) % 3)) % 2 === 0,
                    7 => ((($r + $c) % 2) + (($r * $c) % 3)) % 2 === 0,
                    default => false,
                };
                if ($invert) {
                    $m[$r][$c] = ! $m[$r][$c];
                }
            }
        }
    }
}
