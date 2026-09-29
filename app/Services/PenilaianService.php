<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Penilaian;
use App\Models\PenilaianDetail;
use App\Models\PenilaianKomponen;
use App\Models\Setting;

/**
 * Menghitung nilai akhir PKL dari komponen & kategori penilaian.
 *
 * Formula (bobot dapat diubah admin lewat Pengaturan):
 *   nilai_komponen = Σ( nilai_kategori × bobot_kategori / 100 )
 *   nilai_akhir    = Σ( nilai_komponen × bobot_komponen / 100 )
 */
class PenilaianService
{
    /** Hitung total satu komponen penilaian dari detail kategorinya. */
    public function hitungKomponen(int $penilaianId): float
    {
        $detail = PenilaianDetail::with('kategori')
            ->where('penilaian_id', $penilaianId)
            ->get();

        if ($detail->isEmpty()) {
            return 0;
        }

        $jumlahBobot = $detail->sum(fn($d) => (float) $d->kategori?->bobot);

        if ($jumlahBobot <= 0) {
            // Fallback: rata-rata biasa bila bobot belum diatur
            return round($detail->avg(fn($d) => (float) $d->nilai), 2);
        }

        $total = 0.0;
        foreach ($detail as $d) {
            $bobot = (float) ($d->kategori?->bobot ?? 0);
            $total += ((float) $d->nilai) * ($bobot / max($jumlahBobot, 1));
        }

        return round($total, 2);
    }

    /** Simpan ulang `total` pada satu baris penilaian. */
    public function refreshTotal(Penilaian $penilaian): Penilaian
    {
        $penilaian->update([
            'total' => $this->hitungKomponen($penilaian->id),
            'predikat' => $this->predikat($this->hitungKomponen($penilaian->id)),
        ]);

        return $penilaian->refresh();
    }

    /** Nilai akhir seluruh komponen untuk seorang mahasiswa. */
    public function nilaiAkhir(Mahasiswa $mhs, ?int $periodeId = null): array
    {
        $query = Penilaian::where('mahasiswa_id', $mhs->id)->where('status', 'final');

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $penilaians = $query->get();

        $komponenAktif = PenilaianKomponen::where('aktif', true)->get();
        $totalBobot = $komponenAktif->sum(fn($k) => (float) $k->bobot);

        if ($totalBobot <= 0) {
            $totalBobot = max(1, $penilaians->count());
        }

        $akumulasi = 0.0;
        $terisi = 0;

        foreach ($penilaians as $p) {
            $komponen = $komponenAktif->firstWhere('kode', $p->komponen);
            $bobot = $komponen ? (float) $komponen->bobot : ($totalBobot / max(1, $penilaians->count()));

            $akumulasi += ((float) $p->total) * ($bobot / $totalBobot);
            $terisi++;
        }

        $nilai = round($akumulasi, 2);

        return [
            'nilai' => $nilai,
            'predikat' => $this->predikat($nilai),
            'komponen_terisi' => $terisi,
            'komponen_total' => $komponenAktif->count(),
            'lengkap' => $terisi >= $komponenAktif->count() && $komponenAktif->isNotEmpty(),
        ];
    }

    /** Konversi nilai numerik menjadi predikat huruf. */
    public function predikat(float $nilai): string
    {
        $batasA = (float) Setting::get('nilai_predikat_a', 90);
        $batasB = (float) Setting::get('nilai_predikat_b', 80);
        $batasC = (float) Setting::get('nilai_predikat_c', 70);

        return match (true) {
            $nilai >= $batasA => 'A',
            $nilai >= $batasB => 'B',
            $nilai >= $batasC => 'C',
            default => 'D',
        };
    }

    /** Deskripsi predikat untuk ditampilkan. */
    public function deskripsiPredikat(string $predikat): string
    {
        return match ($predikat) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            default => 'Perlu Bimbingan',
        };
    }
}
