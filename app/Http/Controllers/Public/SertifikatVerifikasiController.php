<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sertifikat;
use App\Models\VerifikasiSertifikat;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman publik verifikasi sertifikat. Tanpa middleware auth.
 */
class SertifikatVerifikasiController extends Controller
{
    public function show(?string $kode = null): View
    {
        if (! $kode) {
            return view('sertifikat.verify', ['sertifikat' => null]);
        }

        $sertifikat = $this->temukan($kode);

        return view('sertifikat.verify', ['sertifikat' => $sertifikat]);
    }

    public function cari(string $kode)
    {
        $sertifikat = $this->temukan($kode);

        return redirect()
            ->route('certificate.verify', $sertifikat?->kode_verifikasi ?? $kode)
            ->with(
                $sertifikat ? 'success' : 'error',
                $sertifikat
                    ? 'Sertifikat ditemukan dan valid.'
                    : 'Sertifikat dengan kode tersebut tidak ditemukan.'
            );
    }

    private function temukan(string $kode): ?Sertifikat
    {
        $sertifikat = Sertifikat::where('kode_verifikasi', strtoupper(trim($kode)))
            ->orWhere('nomor', trim($kode))
            ->with(['mahasiswa', 'periode'])
            ->first();

        if (! $sertifikat) {
            return null;
        }

        // Catat setiap kali sertifikat diverifikasi
        VerifikasiSertifikat::create([
            'sertifikat_id' => $sertifikat->id,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);

        return $sertifikat;
    }
}
