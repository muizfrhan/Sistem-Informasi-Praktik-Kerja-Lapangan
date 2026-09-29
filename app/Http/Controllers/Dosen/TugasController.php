<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\BaseModuleController;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Services\FileStorageService;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TugasController extends BaseModuleController
{
    public function __construct(
        protected FileStorageService $storage,
        protected NotifikasiService $notif
    ) {}

    public function index(Request $request): View
    {
        $dosenId = Auth::user()->dosen?->id;

        $tugas = Tugas::where('dosen_id', $dosenId)
            ->withCount(['targets', 'submissions'])
            ->when($request->filled('search'), fn($q) => $q->where('judul', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status') === 'terlambat') {
                    $q->where('deadline', '<', now());
                } elseif ($request->string('status') === 'akan') {
                    $q->where('deadline', '>=', now());
                }
            })
            ->orderBy('deadline')
            ->paginate(15)
            ->withQueryString();

        return view('dosen.tugas.index', compact('tugas'));
    }

    public function create(): View
    {
        return view('dosen.tugas.create', [
            'mahasiswa' => $this->mahasiswaBimbingan(),
            'periode' => \App\Models\PeriodePkl::aktif(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', 'min:10'],
            'deadline' => ['required', 'date', 'after:now'],
            'wajib' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'array', 'max:3'],
            'attachment.*' => ['file', 'max:5120'],
            'mahasiswa_id' => ['required', 'array', 'min:1'],
            'mahasiswa_id.*' => ['exists:mahasiswa,id'],
        ]);

        $mahasiswaIds = $this->mahasiswaBimbingan()
            ->whereIn('id', $validated['mahasiswa_id'])
            ->pluck('id');

        $attachments = [];
        if ($request->hasFile('attachment')) {
            $attachments = $this->storage->simpanBanyak(
                $request->file('attachment'), 'tugas', 'tugas', Auth::id()
            );
        }

        $tugas = Tugas::create([
            'periode_id' => \App\Models\PeriodePkl::aktif()?->id,
            'dosen_id' => Auth::user()->dosen?->id,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'deadline' => $validated['deadline'],
            'wajib' => $validated['wajib'] ?? true,
            'attachment' => $attachments ?: null,
        ]);

        $tugas->mahasiswa()->sync($mahasiswaIds);

        foreach (Mahasiswa::whereIn('id', $validated['mahasiswa_id'])->with('user')->get() as $mhs) {
            $this->notif->kirim(
                $mhs->user,
                'tugas',
                'Tugas Baru: ' . $tugas->judul,
                'Ada tugas baru dengan deadline ' . $tugas->deadline->format('d M Y H:i') . '.',
                ['tugas_id' => $tugas->id],
                route('mahasiswa.tugas.show', $tugas)
            );
        }

        \App\Models\AuditLog::catat('tugas', 'create', 'Tugas "' . $tugas->judul . '" dibuat untuk ' . $mahasiswaIds->count() . ' mahasiswa');

        return redirect()
            ->route('dosen.tugas.index')
            ->with('success', 'Tugas berhasil dibuat dan dikirim ke ' . $mahasiswaIds->count() . ' mahasiswa.');
    }

    public function show(Tugas $tugas): View
    {
        $this->pastikanMilik($tugas);

        $tugas->load('submissions.mahasiswa');

        return view('dosen.tugas.show', compact('tugas'));
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        $this->pastikanMilik($tugas);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', 'min:10'],
            'deadline' => ['required', 'date'],
            'wajib' => ['nullable', 'boolean'],
        ]);

        $tugas->update($validated);

        \App\Models\AuditLog::catat('tugas', 'update', 'Tugas #' . $tugas->id . ' diperbarui');

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Tugas $tugas): RedirectResponse
    {
        $this->pastikanMilik($tugas);

        if ($tugas->submissions()->where('status', 'completed')->exists()) {
            return back()->with('error', 'Tugas yang sudah dinilai tidak dapat dihapus.');
        }

        foreach ($tugas->attachment ?? [] as $path) {
            $this->storage->hapus($path);
        }

        foreach ($tugas->submissions as $sub) {
            foreach ($sub->file ?? [] as $path) {
                $this->storage->hapus($path);
            }
        }

        $tugas->delete();
        \App\Models\AuditLog::catat('tugas', 'delete', 'Tugas #' . $tugas->id . ' dihapus');

        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    /** Review kiriman tugas mahasiswa. */
    public function review(Request $request, TugasSubmission $submission): RedirectResponse
    {
        $tugas = $submission->tugas;
        $this->pastikanMilik($tugas);

        $validated = $request->validate([
            'status' => ['required', 'in:completed,revision,reviewed'],
            'nilai' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'status' => $validated['status'],
            'nilai' => $validated['nilai'] ?? null,
            'feedback' => $validated['feedback'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        \App\Models\AuditLog::catat('tugas', 'approve', 'Review tugas #' . $submission->id);

        $this->notif->kirim(
            $submission->mahasiswa->user,
            'tugas',
            'Tugas Direview: ' . $tugas->judul,
            'Status tugas Anda: ' . $submission->status
                . ($submission->nilai ? ' dengan nilai ' . $submission->nilai : '')
                . '.',
            ['submission_id' => $submission->id],
            route('mahasiswa.tugas.show', $tugas)
        );

        return back()->with('success', 'Kiriman tugas berhasil direview.');
    }

    private function mahasiswaBimbingan()
    {
        return Mahasiswa::whereIn(
            'id',
            \App\Models\Bimbingan::where('dosen_id', Auth::user()->dosen?->id)
                ->distinct()
                ->pluck('mahasiswa_id')
        )->with('user')->orderBy('nama')->get();
    }

    private function pastikanMilik(Tugas $tugas): void
    {
        abort_unless(
            $tugas->dosen_id === Auth::user()->dosen?->id,
            403,
            'Tugas ini bukan milik Anda.'
        );
    }
}
