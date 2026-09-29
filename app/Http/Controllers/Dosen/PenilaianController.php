<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\BaseModuleController;
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use App\Models\PenilaianKategori;
use App\Services\NotifikasiService;
use App\Services\PenilaianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Penilaian configurable: input nilai per kategori pada komponen tertentu.
 * Dosen hanya boleh menilai mahasiswa bimbingannya sendiri.
 */
class PenilaianController extends BaseModuleController
{
    public function __construct(
        protected PenilaianService $service,
        protected NotifikasiService $notif
    ) {}

    public function index(Request $request): View
    {
        $ids = $this->mahasiswaIds();

        $rows = Penilaian::whereIn('dosen_id', [Auth::user()->dosen?->id])
            ->with('mahasiswa')
            ->when($request->filled('komponen'), fn($q) => $q->where('komponen', $request->string('komponen')))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $menunggu = Mahasiswa::whereIn('id', $ids)
            ->whereNotIn('id', function ($q) {
                $q->select('mahasiswa_id')
                    ->from('penilaian')
                    ->where('dosen_id', Auth::user()->dosen?->id)
                    ->where('komponen', 'pembimbing_sekolah');
            })
            ->orderBy('nama')
            ->get();

        return view('dosen.penilaian.index', compact('rows', 'menunggu'));
    }

    public function create(Mahasiswa $mahasiswa): View
    {
        $this->pastikanBimbingan($mahasiswa);

        $kategori = PenilaianKategori::where('aktif', true)->orderBy('urutan')->get();
        $periode = \App\Models\PeriodePkl::aktif();

        $existing = Penilaian::where('mahasiswa_id', $mahasiswa->id)
            ->where('dosen_id', Auth::user()->dosen?->id)
            ->where('komponen', 'pembimbing_sekolah')
            ->first();

        $nilaiExisting = $existing
            ? \App\Models\PenilaianDetail::where('penilaian_id', $existing->id)->pluck('nilai', 'kategori_id')
            : collect();

        return view('dosen.penilaian.create', compact(
            'mahasiswa', 'kategori', 'periode', 'existing', 'nilaiExisting'
        ));
    }

    public function store(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $this->pastikanBimbingan($mahasiswa);

        $kategori = PenilaianKategori::where('aktif', true)->pluck('id');

        $validated = $request->validate([
            'nilai' => ['required', 'array'],
            'nilai.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'komentar' => ['nullable', 'array'],
            'komentar.*' => ['nullable', 'string', 'max:1000'],
            'komponen' => ['required', 'in:pembimbing_sekolah'],
        ]);

        // Validasi: semua kategori aktif wajib dinilai
        foreach ($kategori as $id) {
            if (! isset($validated['nilai'][$id])) {
                return back()->with('error', 'Semua kategori penilaian wajib diisi.');
            }
        }

        $penilaian = Penilaian::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'periode_id' => \App\Models\PeriodePkl::aktif()?->id,
                'komponen' => $validated['komponen'],
            ],
            [
                'dosen_id' => Auth::user()->dosen?->id,
                'status' => 'draft',
            ]
        );

        foreach ($validated['nilai'] as $kategoriId => $nilai) {
            \App\Models\PenilaianDetail::updateOrCreate(
                ['penilaian_id' => $penilaian->id, 'kategori_id' => $kategoriId],
                ['nilai' => $nilai, 'komentar' => $validated['komentar'][$kategoriId] ?? null]
            );
        }

        $this->service->refreshTotal($penilaian);

        \App\Models\AuditLog::catat('penilaian', 'create', 'Penilaian ' . $validated['komponen'] . ' untuk ' . $mahasiswa->nama);

        return redirect()
            ->route('dosen.penilaian.index')
            ->with('success', 'Penilaian berhasil disimpan. Total: ' . $penilaian->refresh()->total);
    }

    public function finalisasi(Penilaian $penilaian): RedirectResponse
    {
        abort_unless($penilaian->dosen_id === Auth::user()->dosen?->id, 403);

        $penilaian->update([
            'status' => 'final',
            'finalized_by' => Auth::id(),
            'finalized_at' => now(),
        ]);

        \App\Models\AuditLog::catat('penilaian', 'approve', 'Penilaian #' . $penilaian->id . ' difinalisasi');

        $this->notif->kirim(
            $penilaian->mahasiswa->user,
            'penilaian',
            'Hasil Penilaian PKL',
            'Nilai ' . $penilaian->komponen . ' Anda: ' . $penilaian->total . ' (' . $penilaian->predikat . ').',
            ['penilaian_id' => $penilaian->id]
        );

        return back()->with('success', 'Penilaian berhasil difinalisasi.');
    }

    private function mahasiswaIds(): array
    {
        return \App\Models\Bimbingan::where('dosen_id', Auth::user()->dosen?->id)
            ->distinct()
            ->pluck('mahasiswa_id')
            ->all();
    }

    private function pastikanBimbingan(Mahasiswa $mahasiswa): void
    {
        abort_unless(
            in_array($mahasiswa->id, $this->mahasiswaIds(), true),
            403,
            'Mahasiswa ini bukan anak bimbingan Anda.'
        );
    }
}
