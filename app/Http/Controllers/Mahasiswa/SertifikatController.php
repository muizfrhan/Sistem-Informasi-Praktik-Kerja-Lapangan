<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\BaseModuleController;
use App\Models\Sertifikat;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Sertifikat untuk mahasiswa (hanya miliknya sendiri).
 */
class SertifikatController extends BaseModuleController
{
    public function index(): View
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $sertifikat = Sertifikat::where('mahasiswa_id', $mahasiswa?->id)
            ->with('periode')
            ->orderByDesc('id')
            ->get();

        return view('mahasiswa.sertifikat.index', compact('sertifikat'));
    }

    public function show(Sertifikat $sertifikat): View
    {
        $mahasiswa = auth()->user()->mahasiswa;

        abort_unless($sertifikat->mahasiswa_id === $mahasiswa?->id, 403, 'Sertifikat ini bukan milik Anda.');

        $sertifikat->load('periode');

        return view('mahasiswa.sertifikat.show', compact('sertifikat'));
    }

    public function download(Sertifikat $sertifikat)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        abort_unless($sertifikat->mahasiswa_id === $mahasiswa?->id, 403, 'Sertifikat ini bukan milik Anda.');
        abort_unless($sertifikat->status === 'terbit', 403, 'Sertifikat ini belum berlaku.');
        abort_unless($sertifikat->file_pdf, 404, 'Berkas PDF belum tersedia.');

        return Storage::disk('public')->download($sertifikat->file_pdf, $sertifikat->nomor . '.pdf');
    }
}
