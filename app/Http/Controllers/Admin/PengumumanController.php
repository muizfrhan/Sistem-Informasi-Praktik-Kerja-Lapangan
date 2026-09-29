<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use App\Models\Perusahaan;
use App\Models\PeriodePkl;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengumumanController extends BaseModuleController
{
    public function __construct(protected NotifikasiService $notif) {}

    public function index(Request $request): View
    {
        $pengumuman = Pengumuman::with('penulis')
            ->when($request->filled('target'), fn($q) => $q->where('target', $request->string('target')))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function create(): View
    {
        return view('admin.pengumuman.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'min:10', 'max:5000'],
            'target' => ['required', 'in:semua,mahasiswa,dosen,pembimbing_perusahaan,pimpinan,koordinator'],
            'pin' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $pengumuman = Pengumuman::create([
            'user_id' => auth()->id(),
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'target' => $validated['target'],
            'pin' => $validated['pin'] ?? false,
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        // Kirim notifikasi ke seluruh user dalam target
        $targets = \App\Models\User::query()
            ->where('status', 'aktif')
            ->when($validated['target'] !== 'semua', fn($q) => $q->where('role', $validated['target']))
            ->get();

        $this->notif->kirimBanyak(
            $targets,
            'sistem',
            'Pengumuman: ' . $pengumuman->judul,
            \Illuminate\Support\Str::limit($pengumuman->isi, 140),
            ['pengumuman_id' => $pengumuman->id],
            route('notifikasi.index')
        );

        \App\Models\AuditLog::catat('pengumuman', 'create', 'Pengumuman "' . $pengumuman->judul . '" dikirim ke ' . $targets->count() . ' pengguna');

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diterbitkan dan dikirim ke ' . $targets->count() . ' pengguna.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        \App\Models\AuditLog::catat('pengumuman', 'delete', 'Pengumuman #' . $pengumuman->id . ' dihapus');
        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
