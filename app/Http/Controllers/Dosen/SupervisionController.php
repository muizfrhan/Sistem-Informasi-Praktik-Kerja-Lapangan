<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\BaseModuleController;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jurnal;
use App\Models\Mahasiswa;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Modul untuk Dosen Pembimbing: absensi, jurnal, izin, tugas, monitoring.
 * Setiap aksi memverifikasi bahwa data memang milik mahasiswa bimbingannya.
 */
class SupervisionController extends BaseModuleController
{
    public function __construct(protected NotifikasiService $notif) {}

    /** ID mahasiswa yang berada di bawah bimbingan dosen ini. */
    private function mahasiswaIds(): array
    {
        $dosen = Auth::user()->dosen;

        if (! $dosen) {
            return [];
        }

        return \App\Models\Bimbingan::where('dosen_id', $dosen->id)
            ->distinct()
            ->pluck('mahasiswa_id')
            ->all();
    }

    // ------------------------------------------------------------------
    // Dashboard monitoring
    // ------------------------------------------------------------------

    public function dashboard(Request $request): View
    {
        $ids = $this->mahasiswaIds();
        $periode = $this->periode($request);

        $mahasiswa = Mahasiswa::whereIn('id', $ids)
            ->with(['user', 'pendaftaranPkl.perusahaan', 'bimbingan' => fn($q) => $q->where('dosen_id', Auth::user()->dosen?->id)])
            ->paginate(10)
            ->withQueryString();

        $ringkasan = [
            'total' => count($ids),
            'jurnal_pending' => Jurnal::whereIn('mahasiswa_id', $ids)->pending()->count(),
            'izin_pending' => Izin::whereIn('mahasiswa_id', $ids)->pending()->count(),
            'tugas_pending' => \App\Models\TugasSubmission::whereIn('mahasiswa_id', $ids)
                ->where('status', 'submitted')->count(),
        ];

        $monitoring = \App\Models\Monitoring::whereIn('mahasiswa_id', $ids)
            ->where('periode_id', $periode->id)
            ->whereDate('tanggal', today())
            ->with('mahasiswa')
            ->get();

        return view('dosen.monitoring.index', compact('mahasiswa', 'ringkasan', 'monitoring', 'periode'));
    }

    /** Detail satu mahasiswa + timeline aktivitas. */
    public function showMahasiswa(Mahasiswa $mahasiswa): View
    {
        $this->pastikanBimbingan($mahasiswa);
        $periode = $this->periode(request());

        $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->orderByDesc('tanggal')
            ->limit(14)
            ->get();

        $jurnal = Jurnal::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        $izin = Izin::where('mahasiswa_id', $mahasiswa->id)->latest('id')->limit(10)->get();

        $monitoring = \App\Models\Monitoring::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->latest('tanggal')
            ->first();

        $hitung = app(\App\Services\MonitoringService::class)->hitung($mahasiswa, $periode);

        return view('dosen.monitoring.show', compact(
            'mahasiswa', 'periode', 'absensi', 'jurnal', 'izin', 'monitoring', 'hitung'
        ));
    }

    // ------------------------------------------------------------------
    // Jurnal
    // ------------------------------------------------------------------

    public function jurnal(Request $request): View
    {
        $ids = $this->mahasiswaIds();

        $jurnal = Jurnal::whereIn('mahasiswa_id', $ids)
            ->with(['mahasiswa', 'reviewer', 'komentar.user'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('mahasiswa', fn($m) => $q->where('nama', 'like', '%' . $request->string('search') . '%'));
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        $statistik = Jurnal::whereIn('mahasiswa_id', $ids)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('dosen.jurnal.index', compact('jurnal', 'statistik'));
    }

    public function showJurnal(Jurnal $jurnal): View
    {
        $this->pastikanBimbingan(Jurnal::findOrFail($jurnal->mahasiswa_id));

        $jurnal->load(['mahasiswa.user', 'komentar.user', 'reviewer']);

        return view('dosen.jurnal.show', compact('jurnal'));
    }

    public function reviewJurnal(Request $request, Jurnal $jurnal): RedirectResponse
    {
        $this->pastikanBimbingan(Jurnal::findOrFail($jurnal->mahasiswa_id));

        $validated = $request->validate([
            'status' => ['required', 'in:approved,revision,reviewed'],
            'catatan_reviewer' => ['nullable', 'string', 'max:2000'],
        ]);

        $jurnal->update([
            'status' => $validated['status'],
            'catatan_reviewer' => $validated['catatan_reviewer'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'revisi' => $validated['status'] === 'revision' ? $jurnal->revisi + 1 : $jurnal->revisi,
        ]);

        \App\Models\AuditLog::catat('jurnal', 'approve', 'Review jurnal #' . $jurnal->id);

        $this->notif->kirim(
            $jurnal->mahasiswa->user,
            'jurnal',
            'Jurnal Direview: ' . $jurnal->judul,
            'Status jurnal Anda berubah menjadi ' . $jurnal->fresh()->status_label . '.',
            ['jurnal_id' => $jurnal->id],
            route('mahasiswa.jurnal.show', $jurnal)
        );

        return back()->with('success', 'Jurnal berhasil direview.');
    }

    public function komentarJurnal(Request $request, Jurnal $jurnal): RedirectResponse
    {
        $this->pastikanBimbingan(Jurnal::findOrFail($jurnal->mahasiswa_id));

        $validated = $request->validate([
            'komentar' => ['required', 'string', 'max:2000'],
        ]);

        $jurnal->komentar()->create([
            'user_id' => Auth::id(),
            'komentar' => $validated['komentar'],
        ]);

        $this->notif->kirim(
            $jurnal->mahasiswa->user,
            'jurnal',
            'Komentar Baru pada Jurnal',
            'Pembimbing meninggalkan komentar: ' . \Illuminate\Support\Str::limit($validated['komentar'], 80),
            ['jurnal_id' => $jurnal->id],
            route('mahasiswa.jurnal.show', $jurnal)
        );

        return back()->with('success', 'Komentar terkirim ke mahasiswa.');
    }

    // ------------------------------------------------------------------
    // Izin
    // ------------------------------------------------------------------

    public function izin(Request $request): View
    {
        $ids = $this->mahasiswaIds();

        $izin = Izin::whereIn('mahasiswa_id', $ids)
            ->with(['mahasiswa', 'processor'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('tanggal_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('dosen.izin.index', compact('izin'));
    }

    public function prosesIzin(Request $request, Izin $izin): RedirectResponse
    {
        $this->pastikanBimbingan(Izin::findOrFail($izin->mahasiswa_id));

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'alasan_keputusan' => ['nullable', 'string', 'max:1000'],
        ]);

        $izin->update([
            'status' => $validated['status'],
            'alasan_keputusan' => $validated['alasan_keputusan'] ?? null,
            'diproses_oleh' => Auth::id(),
            'diproses_at' => now(),
        ]);

        \App\Models\AuditLog::catat('izin', 'approve', 'Izin #' . $izin->id . ' ' . $validated['status']);

        $this->notif->kirim(
            $izin->mahasiswa->user,
            'approval',
            'Izin ' . ($validated['status'] === 'approved' ? 'Disetujui' : 'Ditolak'),
            'Pengajuan izin Anda ' . ($validated['status'] === 'approved' ? 'telah disetujui.' : 'ditolak.')
                . ($validated['alasan_keputusan'] ? ' Catatan: ' . $validated['alasan_keputusan'] : ''),
            ['izin_id' => $izin->id],
            route('mahasiswa.izin.index')
        );

        return back()->with('success', 'Keputusan izin berhasil disimpan.');
    }

    // ------------------------------------------------------------------
    // Absensi
    // ------------------------------------------------------------------

    public function absensi(Request $request): View
    {
        $ids = $this->mahasiswaIds();
        $periode = $this->periode($request);

        $absensi = Absensi::whereIn('mahasiswa_id', $ids)
            ->where('periode_id', $periode->id)
            ->with('mahasiswa')
            ->when($request->filled('tanggal'), fn($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        $rekap = Absensi::whereIn('mahasiswa_id', $ids)
            ->where('periode_id', $periode->id)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('dosen.absensi.index', compact('absensi', 'rekap', 'periode'));
    }

    public function konfirmasiAbsensi(Request $request, Absensi $absensi): RedirectResponse
    {
        $this->pastikanBimbingan(Absensi::findOrFail($absensi->mahasiswa_id));

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpha,libur'],
        ]);

        $absensi->update([
            'status' => $validated['status'],
            'dikonfirmasi_oleh' => Auth::id(),
        ]);

        \App\Models\AuditLog::catat('absensi', 'approve', 'Konfirmasi absensi #' . $absensi->id);

        return back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    // ------------------------------------------------------------------

    private function pastikanBimbingan(?Mahasiswa $mahasiswa): void
    {
        abort_if(! $mahasiswa, 404, 'Mahasiswa tidak ditemukan.');

        abort_unless(
            in_array($mahasiswa->id, $this->mahasiswaIds(), true),
            403,
            'Mahasiswa ini bukan anak bimbingan Anda.'
        );
    }
}
