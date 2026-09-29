<?php

namespace App\Http\Controllers\Executive;

use App\Http\Controllers\BaseModuleController;
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\LaporanPkl;
use App\Models\Mahasiswa;
use App\Models\Monitoring;
use App\Models\Penilaian;
use App\Models\Perusahaan;
use App\Models\PeriodePkl;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Executive / analytics dashboard untuk Pimpinan (Kepala Sekolah).
 * Hanya membaca data, tidak ada aksi tulis.
 */
class ExecutiveDashboardController extends BaseModuleController
{
    public function __construct(protected PenilaianService $penilaian) {}

    public function index(Request $request): View
    {
        $periode = $this->periode($request);

        $mhsIds = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['diterima', 'placed', 'active', 'completed'])
            ->pluck('mahasiswa_id');

        $totalPeserta = $mhsIds->count();
        $selesai = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['completed'])->count();

        $totalAbsensi = Absensi::where('periode_id', $periode->id)->count();
        $hadir = Absensi::where('periode_id', $periode->id)
            ->whereIn('status', ['hadir', 'terlambat'])->count();
        $sah = Absensi::where('periode_id', $periode->id)
            ->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'libur'])->count();

        $nilaiTersedia = Penilaian::where('periode_id', $periode->id)->where('status', 'final')->get();
        $rataNilai = $nilaiTersedia->isEmpty() ? 0 : round($nilaiTersedia->avg('total'), 2);

        $monitoringHariIni = Monitoring::where('periode_id', $periode->id)
            ->whereDate('tanggal', today())
            ->get();

        $kpi = [
            'total_peserta' => $totalPeserta,
            'total_perusahaan' => Perusahaan::where('status_kerja_sama', '!=', 'nonaktif')->count(),
            'completion_rate' => $totalPeserta > 0 ? round(($selesai / $totalPeserta) * 100, 1) : 0,
            'average_score' => $rataNilai,
            'attendance_rate' => $totalAbsensi > 0 ? round(($sah / $totalAbsensi) * 100, 1) : 0,
            'issue_rate' => $totalPeserta > 0
                ? round((($monitoringHariIni->whereIn('indikator', ['warning', 'critical'])->count()) / max(1, $monitoringHariIni->count())) * 100, 1)
                : 0,
        ];

        // Distribusi per jurusan
        $perJurusan = Mahasiswa::whereIn('id', $mhsIds)
            ->join('jurusan', 'mahasiswa.jurusan_id', '=', 'jurusan.id')
            ->selectRaw('jurusan.nama, count(*) as jumlah')
            ->groupBy('jurusan.nama')
            ->orderByDesc('jumlah')
            ->get();

        // Distribusi per perusahaan
        $perPerusahaan = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['diterima', 'placed', 'active', 'completed'])
            ->join('perusahaan', 'pendaftaran_pkl.perusahaan_id', '=', 'perusahaan.id')
            ->selectRaw('perusahaan.nama, count(*) as jumlah')
            ->groupBy('perusahaan.nama')
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        // Sebaran status
        $statusPkl = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        // Sebaran kehadiran
        $sebaranAbsensi = Absensi::where('periode_id', $periode->id)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        // Sebaran predikat
        $sebaranPredikat = $nilaiTersedia->pluck('predikat')->countBy();

        // Tren bulanan
        $tren = Absensi::where('periode_id', $periode->id)
            ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan, count(*) as total,
                         sum(status in ("hadir","terlambat")) as hadir')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $jurnalStat = Jurnal::where('periode_id', $periode->id)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('pimpinan.dashboard', compact(
            'kpi', 'perJurusan', 'perPerusahaan', 'statusPkl', 'sebaranAbsensi',
            'sebaranPredikat', 'tren', 'jurnalStat', 'periode', 'monitoringHariIni'
        ));
    }

    public function laporan(Request $request): View
    {
        $periode = $this->periode($request);

        $rekap = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->with(['mahasiswa', 'perusahaan'])
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('pimpinan.laporan', compact('rekap', 'periode'));
    }

    /** Ekspor rekap ke CSV. */
    public function export(Request $request, string $jenis)
    {
        $periode = $this->periode($request);

        $nama = match ($jenis) {
            'peserta' => 'rekap-peserta-' . $periode->kode,
            'perusahaan' => 'rekap-perusahaan-' . $periode->kode,
            default => abort(404, 'Jenis ekspor tidak dikenal.'),
        };

        if ($jenis === 'peserta') {
            $rows = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
                ->with(['mahasiswa', 'perusahaan'])
                ->get();

            $header = ['NIM', 'Nama', 'Program Studi', 'Kelas', 'Perusahaan', 'Bidang PKL', 'Status'];
            $data = $rows->map(fn($r) => [
                $r->mahasiswa?->nim,
                $r->mahasiswa?->nama,
                $r->mahasiswa?->program_studi,
                $r->mahasiswa?->kelas,
                $r->perusahaan?->nama,
                $r->bidang_pkl,
                $r->status_label,
            ])->all();
        } else {
            $rows = Perusahaan::withCount('pendaftaranPkl')->get();
            $header = ['Perusahaan', 'Bidang', 'Kota', 'Kuota', 'Terpakai', 'Status Kerja Sama'];
            $data = $rows->map(fn($p) => [
                $p->nama, $p->bidang, $p->kota, $p->kuota_siswa,
                $p->jumlahSiswaAktif(), $p->status_kerja_sama,
            ])->all();
        }

        return response()->streamDownload(function () use ($header, $data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $header);
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $nama . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
