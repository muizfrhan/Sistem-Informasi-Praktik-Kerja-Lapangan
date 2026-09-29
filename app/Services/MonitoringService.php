<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Bimbingan;
use App\Models\Izin;
use App\Models\Jurnal;
use App\Models\LaporanPkl;
use App\Models\Mahasiswa;
use App\Models\Monitoring;
use App\Models\PeriodePkl;

/**
 * Menghitung progres & indikator kesehatan seorang mahasiswa.
 * Semua angka berasal dari database, bukan hard-code.
 */
class MonitoringService
{
    /** Ambang batas untuk indikator. */
    private const BATAS_WARNING = 70.0;
    private const BATAS_CRITICAL = 50.0;

    /**
     * Hitung progres lengkap seorang mahasiswa pada satu periode.
     *
     * @return array{progres_pkl:float, progres_absensi:float, progres_jurnal:float,
     *               progres_bimbingan:float, progres_laporan:float, indikator:string,
     *               rincian:array}
     */
    public function hitung(Mahasiswa $mahasiswa, PeriodePkl $periode): array
    {
        $hariEfektif = $this->hitungHariEfektif($periode, $mahasiswa);

        $progresPkl = $this->hitungProgresWaktu($periode);
        $progresAbsensi = $this->hitungAbsensi($mahasiswa, $periode, $hariEfektif);
        $progresJurnal = $this->hitungJurnal($mahasiswa, $periode, $hariEfektif);
        $progresBimbingan = $this->hitungBimbingan($mahasiswa);
        $progresLaporan = $this->hitungLaporan($mahasiswa);

        $skor = ($progresPkl + $progresAbsensi + $progresJurnal + $progresBimbingan + $progresLaporan) / 5;

        return [
            'progres_pkl' => round($progresPkl, 2),
            'progres_absensi' => round($progresAbsensi, 2),
            'progres_jurnal' => round($progresJurnal, 2),
            'progres_bimbingan' => round($progresBimbingan, 2),
            'progres_laporan' => round($progresLaporan, 2),
            'skor' => round($skor, 2),
            'indikator' => $this->indikator($skor),
            'rincian' => [
                'hari_efektif' => $hariEfektif,
                'total_absensi' => Absensi::where('mahasiswa_id', $mahasiswa->id)
                    ->where('periode_id', $periode->id)->count(),
                'total_jurnal' => Jurnal::where('mahasiswa_id', $mahasiswa->id)
                    ->where('periode_id', $periode->id)->count(),
                'jurnal_approved' => Jurnal::where('mahasiswa_id', $mahasiswa->id)
                    ->where('periode_id', $periode->id)->where('status', 'approved')->count(),
                'total_bimbingan' => Bimbingan::where('mahasiswa_id', $mahasiswa->id)->count(),
                'bimbingan_disetujui' => Bimbingan::where('mahasiswa_id', $mahasiswa->id)
                    ->disetujui()->count(),
            ],
        ];
    }

    /** Simpan hasil hitungan ke tabel monitoring (upsert harian). */
    public function simpan(Mahasiswa $mahasiswa, PeriodePkl $periode, ?string $tanggal = null): Monitoring
    {
        $hasil = $this->hitung($mahasiswa, $periode);

        return Monitoring::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'periode_id' => $periode->id,
                'tanggal' => $tanggal ?? today(),
            ],
            [
                'progres_pkl' => $hasil['progres_pkl'],
                'progres_absensi' => $hasil['progres_absensi'],
                'progres_jurnal' => $hasil['progres_jurnal'],
                'progres_bimbingan' => $hasil['progres_bimbingan'],
                'progres_laporan' => $hasil['progres_laporan'],
                'indikator' => $hasil['indikator'],
                'catatan' => json_encode($hasil['rincian']),
            ]
        );
    }

    /** Hitung ulang monitoring untuk semua mahasiswa pada satu periode. */
    public function segarkanPeriode(PeriodePkl $periode): int
    {
        $jumlah = 0;

        $mhsIds = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['diterima', 'placed', 'active'])
            ->pluck('mahasiswa_id');

        foreach ($mhsIds as $mhsId) {
            $mahasiswa = Mahasiswa::find($mhsId);
            if (! $mahasiswa) {
                continue;
            }
            $this->simpan($mahasiswa, $periode);
            $jumlah++;
        }

        return $jumlah;
    }

    // ------------------------------------------------------------------
    // Komponen perhitungan
    // ------------------------------------------------------------------

    private function hitungProgresWaktu(PeriodePkl $periode): float
    {
        $total = max(1, $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) + 1);
        $lewat = min($total, max(0, $periode->tanggal_mulai->diffInDays(today())));

        return round(($lewat / $total) * 100, 2);
    }

    private function hitungAbsensi(Mahasiswa $mhs, PeriodePkl $periode, int $hariEfektif): float
    {
        if ($hariEfektif <= 0) {
            return 0;
        }

        $hadir = Absensi::where('mahasiswa_id', $mhs->id)
            ->where('periode_id', $periode->id)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();

        $izin = Absensi::where('mahasiswa_id', $mhs->id)
            ->where('periode_id', $periode->id)
            ->whereIn('status', ['izin', 'sakit', 'libur'])
            ->count();

        // Izin/sakit tetap dihitung sebagai kehadiran yang sah
        $terhitung = min($hariEfektif, $hadir + $izin);

        return round(($terhitung / $hariEfektif) * 100, 2);
    }

    private function hitungJurnal(Mahasiswa $mhs, PeriodePkl $periode, int $hariEfektif): float
    {
        if ($hariEfektif <= 0) {
            return 0;
        }

        $jurnal = Jurnal::where('mahasiswa_id', $mhs->id)
            ->where('periode_id', $periode->id)
            ->where('status', '!=', 'draft')
            ->count();

        return round((min($jurnal, $hariEfektif) / $hariEfektif) * 100, 2);
    }

    private function hitungBimbingan(Mahasiswa $mhs): float
    {
        $disetujui = Bimbingan::where('mahasiswa_id', $mhs->id)->disetujui()->count();

        // Target default: 8 sesi disetujui dalam satu periode
        $target = max(1, (int) config('sipkl.target_bimbingan', 8));

        return round(min($disetujui / $target, 1) * 100, 2);
    }

    private function hitungLaporan(Mahasiswa $mhs): float
    {
        $laporan = LaporanPkl::where('mahasiswa_id', $mhs->id)->latest('id')->first();

        if (! $laporan) {
            return 0;
        }

        return match ($laporan->status) {
            'diterima' => 100,
            'pending' => 70,
            'ditolak' => 40,
            default => 0,
        };
    }

    private function hitungHariEfektif(PeriodePkl $periode, Mahasiswa $mhs): int
    {
        $mulai = max($periode->tanggal_mulai, today());
        $selesai = min($periode->tanggal_selesai, today());

        if ($selesai->lt($mulai)) {
            return 0;
        }

        $total = $mulai->diffInDays($selesai) + 1;

        // Kurangi hari yang disetujui sebagai izin/sakit
        $izinHari = Izin::where('mahasiswa_id', $mhs->id)
            ->where('status', 'approved')
            ->whereBetween('tanggal_mulai', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->get()
            ->sum(fn($i) => $i->hari);

        return max(0, $total - $izinHari);
    }

    public function indikator(float $skor): string
    {
        if ($skor >= self::BATAS_WARNING) {
            return 'healthy';
        }

        if ($skor >= self::BATAS_CRITICAL) {
            return 'warning';
        }

        return 'critical';
    }
}
