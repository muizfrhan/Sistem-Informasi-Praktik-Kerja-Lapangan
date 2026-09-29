<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\BaseModuleController;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Mahasiswa;
use App\Models\PeriodePkl;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AbsensiController extends BaseModuleController
{
    /** Dashboard absensi + tombol check in/out. */
    public function index(Request $request): View
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $periode = $this->periode($request);
        $today = today();

        $hariIni = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->whereDate('tanggal', $today)
            ->first();

        $izinAktif = $mahasiswa->izinAktif();

        $riwayat = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $totalAbsensi = $rekap->sum();
        $terhitung = $rekap->only(['hadir', 'terlambat', 'izin', 'sakit', 'libur'])->sum();
        $persentase = $totalAbsensi > 0 ? round(($terhitung / $totalAbsensi) * 100, 1) : 0;

        return view('mahasiswa.absensi.index', compact(
            'mahasiswa', 'periode', 'hariIni', 'izinAktif', 'riwayat', 'rekap',
            'persentase', 'totalAbsensi'
        ));
    }

    /** Check in. */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $mahasiswa = Auth::user()->mahasiswa;
        $periode = $this->periode($request);

        if (! $periode->sedangBerjalan()) {
            return back()->with('error', 'Absensi hanya dapat dilakukan pada tanggal periode PKL.');
        }

        if (Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->whereDate('tanggal', today())
            ->exists()) {
            return back()->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        $jamMasuk = now()->format('H:i:s');
        $batasMasuk = Setting::get('pkl_jam_masuk', '08:00');
        $toleransi = (int) Setting::get('pkl_toleransi_telat', 15);

        $batas = Carbon::createFromFormat('H:i', $batasMasuk);
        $terlambat = now()->greaterThan($batas->copy()->addMinutes($toleransi));

        $dalamRadius = $this->cekRadius($periode, $validated);

        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'periode_id' => $periode->id,
            'tanggal' => today(),
            'jam_masuk' => $jamMasuk,
            'status' => $terlambat ? 'terlambat' : 'hadir',
            'catatan' => $validated['catatan'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'dalam_radius' => $dalamRadius,
            'izin_id' => $mahasiswa->izinAktif()?->id,
        ]);

        \App\Models\AuditLog::catat('absensi', 'create', 'Check-in ' . $mahasiswa->nama, [
            'tanggal' => today()->toDateString(),
            'status' => $terlambat ? 'terlambat' : 'hadir',
        ]);

        return back()->with('success', 'Check-in berhasil dicatat pukul ' . now()->format('H:i') . '.');
    }

    /** Check out. */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $mahasiswa = Auth::user()->mahasiswa;
        $periode = $this->periode($request);

        $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->whereDate('tanggal', today())
            ->first();

        if (! $absensi) {
            return back()->with('error', 'Anda belum melakukan check-in hari ini.');
        }

        if ($absensi->jam_pulang) {
            return back()->with('error', 'Anda sudah melakukan check-out hari ini.');
        }

        $absensi->update([
            'jam_pulang' => now()->format('H:i:s'),
            'durasi_menit' => $absensi->hitungDurasi(),
            'catatan' => $validated['catatan'] ?? $absensi->catatan,
        ]);

        \App\Models\AuditLog::catat('absensi', 'update', 'Check-out ' . $mahasiswa->nama);

        return back()->with('success', 'Check-out berhasil dicatat pukul ' . now()->format('H:i') . '.');
    }

    /** Rekap bulanan + kalender. */
    public function rekap(Request $request): View
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $periode = $this->periode($request);

        $bulan = (int) $request->integer('bulan', now()->month);
        $tahun = (int) $request->integer('tahun', now()->year);
        $awal = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();

        $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->orderBy('tanggal')
            ->get();

        $rekap = $absensi->groupBy('status')->map->count();

        $totalHariKerja = 0;
        for ($d = $awal->copy(); $d->lte($akhir); $d->addDay()) {
            if ($d->isWeekend()) {
                continue;
            }
            $totalHariKerja++;
        }

        $hadir = ($rekap['hadir'] ?? 0) + ($rekap['terlambat'] ?? 0);
        $persentase = $totalHariKerja > 0 ? round(($hadir / $totalHariKerja) * 100, 1) : 0;

        return view('mahasiswa.absensi.rekap', compact(
            'mahasiswa', 'periode', 'absensi', 'rekap', 'bulan', 'tahun',
            'totalHariKerja', 'hadir', 'persentase', 'awal'
        ));
    }

    private function cekRadius(PeriodePkl $periode, array $validated): ?bool
    {
        $lokasi = $periode->pendaftaran()->first()?->perusahaan?->lokasi;

        if (! $lokasi || ! isset($validated['latitude'], $validated['longitude'])) {
            return null;
        }

        $jarak = $this->haversine(
            (float) $validated['latitude'], (float) $validated['longitude'],
            (float) $lokasi->latitude, (float) $lokasi->longitude
        );

        return $jarak <= (int) $lokasi->radius_meter;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
