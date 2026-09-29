<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\Mahasiswa;
use App\Models\Sertifikat;
use App\Services\NotifikasiService;
use App\Services\PenilaianService;
use App\Services\SertifikatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SertifikatController extends BaseModuleController
{
    public function __construct(
        protected SertifikatService $sertifikat,
        protected PenilaianService $penilaian,
        protected NotifikasiService $notif
    ) {}

    public function index(Request $request): View
    {
        $sertifikat = Sertifikat::with(['mahasiswa', 'periode', 'penerbit'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->toString();
                $q->whereHas('mahasiswa', fn($m) => $m->where('nama', 'like', "%{$s}%"))
                    ->orWhere('nomor', 'like', "%{$s}%");
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sertifikat.index', compact('sertifikat'));
    }

    public function create(): View
    {
        $periode = $this->periode(request());

        // Mahasiswa yang PKL-nya sudah selesai (periode selesai/evaluasi + laporan diterima)
        $kandidat = Mahasiswa::whereIn('id', \App\Models\PendaftaranPkl::where('periode_id', $periode->id)
            ->whereIn('status', ['diterima', 'placed', 'active', 'completed'])
            ->pluck('mahasiswa_id'))
            ->whereDoesntHave('sertifikat', fn($q) => $q->where('periode_id', $periode->id))
            ->orderBy('nama')
            ->get();

        return view('admin.sertifikat.create', compact('kandidat', 'periode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mahasiswa_id' => ['required', 'exists:mahasiswa,id'],
            'periode_id' => ['required', 'exists:periode_pkl,id'],
        ]);

        $periode = \App\Models\PeriodePkl::findOrFail($validated['periode_id']);
        $mahasiswa = Mahasiswa::findOrFail($validated['mahasiswa_id']);

        if (Sertifikat::where('mahasiswa_id', $mahasiswa->id)->where('periode_id', $periode->id)->exists()) {
            return back()->with('error', 'Mahasiswa ini sudah memiliki sertifikat untuk periode tersebut.');
        }

        $sertifikat = $this->sertifikat->terbitkan($mahasiswa, $periode, $this->penilaian);

        \App\Models\AuditLog::catat(
            'sertifikat',
            'create',
            'Sertifikat ' . $sertifikat->nomor . ' diterbitkan untuk ' . $mahasiswa->nama
        );

        $this->notif->kirim(
            $mahasiswa->user,
            'sistem',
            'Sertifikat PKL Terbit',
            'Sertifikat PKL Anda dengan nomor ' . $sertifikat->nomor . ' telah diterbitkan.',
            ['sertifikat_id' => $sertifikat->id],
            route('mahasiswa.sertifikat.show', $sertifikat)
        );

        return redirect()
            ->route('admin.sertifikat.index')
            ->with('success', 'Sertifikat ' . $sertifikat->nomor . ' berhasil diterbitkan.');
    }

    public function show(Sertifikat $sertifikat): View
    {
        $sertifikat->load(['mahasiswa.pendaftaranPkl.perusahaan', 'periode', 'penerbit', 'verifikasiLogs']);

        return view('admin.sertifikat.show', compact('sertifikat'));
    }

    public function download(Sertifikat $sertifikat)
    {
        abort_unless($sertifikat->file_pdf, 404, 'Berkas PDF belum tersedia.');

        return Storage::disk('public')->download($sertifikat->file_pdf, $sertifikat->nomor . '.pdf');
    }

    public function cabut(Request $request, Sertifikat $sertifikat): RedirectResponse
    {
        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        $sertifikat->update(['status' => 'dicabut']);

        \App\Models\AuditLog::catat(
            'sertifikat',
            'delete',
            'Sertifikat ' . $sertifikat->nomor . ' dicabut. Alasan: ' . $validated['alasan']
        );

        return back()->with('success', 'Sertifikat berhasil dicabut.');
    }

    /** QR code sebagai SVG (untuk dicetak/disisipkan). */
    public function qr(Sertifikat $sertifikat)
    {
        $matrix = $this->sertifikat->qrMatrix(
            route('certificate.verify', $sertifikat->kode_verifikasi),
            4
        );

        $size = count($matrix);
        $svg = view('sertifikat.qr', compact('matrix', 'size'))->render();

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr-' . $sertifikat->nomor . '.svg"',
        ]);
    }
}
