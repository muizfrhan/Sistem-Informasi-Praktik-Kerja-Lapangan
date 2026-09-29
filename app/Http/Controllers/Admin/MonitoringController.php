<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jurnal;
use App\Models\LaporanPkl;
use App\Models\Mahasiswa;
use App\Models\Monitoring as MonitoringModel;
use App\Models\PeriodePkl;
use App\Models\TugasSubmission;
use App\Services\MonitoringService;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends BaseModuleController
{
    public function __construct(
        protected MonitoringService $monitoring,
        protected NotifikasiService $notif
    ) {}

    public function index(Request $request): View
    {
        $periode = $this->periode($request);

        $rows = MonitoringModel::where('periode_id', $periode->id)
            ->whereDate('tanggal', today())
            ->with(['mahasiswa.user', 'mahasiswa.pendaftaranPkl.perusahaan'])
            ->when($request->filled('indikator'), fn($q) => $q->where('indikator', $request->string('indikator')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->toString();
                $q->whereHas('mahasiswa', fn($m) => $m->where('nama', 'like', "%{$s}%"));
            })
            ->orderBy('skor_keseluruhan')
            ->paginate(20)
            ->withQueryString();

        $ringkasan = [
            'total' => MonitoringModel::where('periode_id', $periode->id)->whereDate('tanggal', today())->count(),
            'healthy' => MonitoringModel::where('periode_id', $periode->id)
                ->whereDate('tanggal', today())->where('indikator', 'healthy')->count(),
            'warning' => MonitoringModel::where('periode_id', $periode->id)
                ->whereDate('tanggal', today())->where('indikator', 'warning')->count(),
            'critical' => MonitoringModel::where('periode_id', $periode->id)
                ->whereDate('tanggal', today())->where('indikator', 'critical')->count(),
        ];

        return view('admin.monitoring.index', compact('rows', 'ringkasan', 'periode'));
    }

    public function koordinator(Request $request): View
    {
        $periode = $this->periode($request);

        $mhsIds = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['diterima', 'placed', 'active'])
            ->pluck('mahasiswa_id');

        $antrean = [
            'pendaftaran' => \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
                ->where('status', 'menunggu')->count(),
            'jurnal' => Jurnal::whereIn('mahasiswa_id', $mhsIds)->pending()->count(),
            'izin' => Izin::whereIn('mahasiswa_id', $mhsIds)->pending()->count(),
            'laporan' => LaporanPkl::whereIn('mahasiswa_id', $mhsIds)->pending()->count(),
            'tugas' => TugasSubmission::whereIn('mahasiswa_id', $mhsIds)->where('status', 'submitted')->count(),
        ];

        $perusahaan = \App\Models\Perusahaan::withCount('pendaftaranPkl')
            ->orderByDesc('pendaftaran_pkl_count')
            ->limit(8)
            ->get();

        $peringatan = \App\Models\Peringatan::with('mahasiswa')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('koordinator.dashboard', compact('antrean', 'perusahaan', 'peringatan', 'periode'));
    }

    public function show(Mahasiswa $mahasiswa, Request $request): View
    {
        $periode = $this->periode($request);
        $hasil = $this->monitoring->hitung($mahasiswa, $periode);

        $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->orderByDesc('tanggal')->limit(30)->get();

        $jurnal = Jurnal::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->orderByDesc('tanggal')->limit(20)->get();

        $timeline = $this->timeline($mahasiswa, $periode);

        return view('admin.monitoring.show', compact(
            'mahasiswa', 'periode', 'hasil', 'absensi', 'jurnal', 'timeline'
        ));
    }

    /** Hitung ulang monitoring seluruh mahasiswa pada periode aktif. */
    public function segarkan(Request $request): RedirectResponse
    {
        $periode = $this->periode($request);
        $jumlah = $this->monitoring->segarkanPeriode($periode);

        $mhsIds = \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['diterima', 'placed', 'active'])
            ->pluck('mahasiswa_id');

        $batasJurnal = (int) config('sipkl.batas_jurnal_hari', 3);
        $batasAbsensi = (float) config('sipkl.batas_absensi_persen', 80);

        foreach (Mahasiswa::whereIn('id', $mhsIds)->get() as $mhs) {
            $this->notif->periksaPeringatan($mhs, $batasJurnal, $batasAbsensi);
        }

        \App\Models\AuditLog::catat('monitoring', 'update', "Monitoring {$jumlah} mahasiswa disegarkan");

        return back()->with('success', "Monitoring berhasil disegarkan untuk {$jumlah} mahasiswa.");
    }

    /** Timeline aktivitas untuk halaman detail. */
    private function timeline(Mahasiswa $mhs, PeriodePkl $periode): array
    {
        $items = [];

        $pendaftaran = $mhs->pendaftaranPkl;
        if ($pendaftaran) {
            $items[] = [
                'waktu' => $pendaftaran->created_at,
                'judul' => 'Pendaftaran PKL dibuat',
                'detail' => 'Perusahaan: ' . ($pendaftaran->perusahaan?->nama ?? '-'),
                'warna' => 'blue',
            ];
            if ($pendaftaran->sudahDiterima()) {
                $items[] = [
                    'waktu' => $pendaftaran->updated_at,
                    'judul' => 'Pendaftaran disetujui',
                    'detail' => 'Status: ' . $pendaftaran->status_label,
                    'warna' => 'emerald',
                ];
            }
        }

        $jurnal = Jurnal::where('mahasiswa_id', $mhs->id)->orderBy('tanggal')->first();
        if ($jurnal) {
            $items[] = [
                'waktu' => $jurnal->created_at,
                'judul' => 'Jurnal pertama',
                'detail' => $jurnal->judul,
                'warna' => 'violet',
            ];
        }

        foreach (\App\Models\Bimbingan::where('mahasiswa_id', $mhs->id)->disetujui()->limit(3)->get() as $b) {
            $items[] = [
                'waktu' => $b->created_at,
                'judul' => 'Bimbingan disetujui',
                'detail' => $b->tanggal_bimbingan->format('d M Y') . ' — ' . ($b->dosen?->nama ?? '-'),
                'warna' => 'cyan',
            ];
        }

        foreach ($mhs->absensi()->whereNotNull('jam_masuk')->orderBy('tanggal')->limit(1)->get() as $a) {
            $items[] = [
                'waktu' => $a->created_at,
                'judul' => 'Absensi pertama',
                'detail' => $a->tanggal->format('d M Y') . ' pukul ' . $a->jam_masuk,
                'warna' => 'amber',
            ];
        }

        $laporan = $mhs->laporanPkl()->latest('id')->first();
        if ($laporan) {
            $items[] = [
                'waktu' => $laporan->created_at,
                'judul' => 'Laporan PKL diunggah',
                'detail' => 'Status: ' . $laporan->status_label,
                'warna' => 'blue',
            ];
        }

        $penilaian = $mhs->penilaian()->where('status', 'final')->first();
        if ($penilaian) {
            $items[] = [
                'waktu' => $penilaian->finalized_at,
                'judul' => 'Penilaian difinalisasi',
                'detail' => 'Nilai: ' . $penilaian->total . ' (' . $penilaian->predikat . ')',
                'warna' => 'emerald',
            ];
        }

        $sertifikat = $mhs->sertifikat()->where('status', 'terbit')->first();
        if ($sertifikat) {
            $items[] = [
                'waktu' => $sertifikat->diterbitkan_at,
                'judul' => 'Sertifikat diterbitkan',
                'detail' => $sertifikat->nomor,
                'warna' => 'emerald',
            ];
        }

        usort($items, fn($a, $b) => $b['waktu'] <=> $a['waktu']);

        return $items;
    }
}
