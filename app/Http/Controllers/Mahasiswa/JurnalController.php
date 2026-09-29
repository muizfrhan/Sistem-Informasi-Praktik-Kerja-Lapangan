<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\BaseModuleController;
use App\Models\Jurnal;
use App\Models\JurnalKomentar;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JurnalController extends BaseModuleController
{
    public function __construct(protected FileStorageService $storage)
    {
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $periode = $this->periode($request);

        $jurnal = Jurnal::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->with(['komentar.user', 'reviewer'])
            ->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();

        $statistik = Jurnal::where('mahasiswa_id', $mahasiswa->id)
            ->where('periode_id', $periode->id)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('mahasiswa.jurnal.index', compact('mahasiswa', 'periode', 'jurnal', 'statistik'));
    }

    public function create(Request $request): View
    {
        $periode = $this->periode($request);

        return view('mahasiswa.jurnal.create', compact('periode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', 'min:10'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'output' => ['nullable', 'string', 'max:5000'],
            'kendala' => ['nullable', 'string', 'max:5000'],
            'solusi' => ['nullable', 'string', 'max:5000'],
            'dokumentasi' => ['nullable', 'array', 'max:5'],
            'dokumentasi.*' => ['file', 'max:5120'],
            'status' => ['nullable', 'in:draft,submitted'],
        ]);

        $mahasiswa = Auth::user()->mahasiswa;
        $periode = $this->periode($request);

        if (! $periode->sedangBerjalan()) {
            return back()->with('error', 'Jurnal hanya dapat dibuat pada masa pelaksanaan PKL.');
        }

        $paths = [];
        if ($request->hasFile('dokumentasi')) {
            $paths = $this->storage->simpanBanyak(
                $request->file('dokumentasi'), 'jurnal', 'jurnal', $mahasiswa->user_id
            );
        }

        Jurnal::create([
            'mahasiswa_id' => $mahasiswa->id,
            'periode_id' => $periode->id,
            'tanggal' => $validated['tanggal'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'output' => $validated['output'] ?? null,
            'kendala' => $validated['kendala'] ?? null,
            'solusi' => $validated['solusi'] ?? null,
            'dokumentasi' => $paths ?: null,
            'status' => $validated['status'] ?? 'submitted',
        ]);

        \App\Models\AuditLog::catat('jurnal', 'create', 'Jurnal "' . $validated['judul'] . '" oleh ' . $mahasiswa->nama);

        return redirect()
            ->route('mahasiswa.jurnal.index')
            ->with('success', 'Jurnal berhasil disimpan dan dikirim ke pembimbing.');
    }

    public function show(Request $request, Jurnal $jurnal): View
    {
        $this->pastikanMilik($jurnal);

        $jurnal->load(['komentar.user', 'reviewer', 'mahasiswa']);

        return view('mahasiswa.jurnal.show', compact('jurnal'));
    }

    public function edit(Request $request, Jurnal $jurnal): View
    {
        $this->pastikanMilik($jurnal);

        if (in_array($jurnal->status, ['approved', 'reviewed'], true)) {
            return back()->with('error', 'Jurnal yang sudah direview tidak dapat diubah.');
        }

        return view('mahasiswa.jurnal.edit', compact('jurnal'));
    }

    public function update(Request $request, Jurnal $jurnal): RedirectResponse
    {
        $this->pastikanMilik($jurnal);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', 'min:10'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'output' => ['nullable', 'string', 'max:5000'],
            'kendala' => ['nullable', 'string', 'max:5000'],
            'solusi' => ['nullable', 'string', 'max:5000'],
            'dokumentasi' => ['nullable', 'array', 'max:5'],
            'dokumentasi.*' => ['file', 'max:5120'],
        ]);

        $jurnal->fill($validated)->save();

        if ($request->hasFile('dokumentasi')) {
            foreach ($jurnal->dokumentasi ?? [] as $path) {
                $this->storage->hapus($path);
            }
            $jurnal->dokumentasi = $this->storage->simpanBanyak(
                $request->file('dokumentasi'), 'jurnal', 'jurnal', $jurnal->mahasiswa->user_id
            );
        }

        if ($jurnal->wasChanged('deskripsi') || $jurnal->wasChanged('judul')) {
            $jurnal->update(['status' => 'submitted', 'revisi' => $jurnal->revisi + 1]);
        }

        \App\Models\AuditLog::catat('jurnal', 'update', 'Jurnal #' . $jurnal->id . ' diperbarui');

        return redirect()
            ->route('mahasiswa.jurnal.index')
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(Jurnal $jurnal): RedirectResponse
    {
        $this->pastikanMilik($jurnal);

        if ($jurnal->status === 'approved') {
            return back()->with('error', 'Jurnal yang sudah disetujui tidak dapat dihapus.');
        }

        foreach ($jurnal->dokumentasi ?? [] as $path) {
            $this->storage->hapus($path);
        }

        $jurnal->delete();
        \App\Models\AuditLog::catat('jurnal', 'delete', 'Jurnal #' . $jurnal->id . ' dihapus');

        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    /** Tambah komentar pada jurnal sendiri (untuk tanya jawab dengan pembimbing). */
    public function komentar(Request $request, Jurnal $jurnal): RedirectResponse
    {
        $this->pastikanMilik($jurnal);

        $validated = $request->validate([
            'komentar' => ['required', 'string', 'max:2000'],
        ]);

        JurnalKomentar::create([
            'jurnal_id' => $jurnal->id,
            'user_id' => Auth::id(),
            'komentar' => $validated['komentar'],
        ]);

        return back()->with('success', 'Komentar terkirim.');
    }

    private function pastikanMilik(Jurnal $jurnal): void
    {
        $mahasiswa = Auth::user()->mahasiswa;

        abort_unless(
            $mahasiswa && $jurnal->mahasiswa_id === $mahasiswa->id,
            403,
            'Jurnal ini bukan milik Anda.'
        );
    }
}
