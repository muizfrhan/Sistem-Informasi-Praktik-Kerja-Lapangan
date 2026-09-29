<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\BaseModuleController;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TugasController extends BaseModuleController
{
    public function __construct(protected FileStorageService $storage) {}

    public function index(Request $request): View
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $tugas = Tugas::whereHas('targets', fn($q) => $q->where('mahasiswa_id', $mahasiswa->id))
            ->with(['dosen', 'submissions' => fn($q) => $q->where('mahasiswa_id', $mahasiswa->id)])
            ->orderBy('deadline')
            ->paginate(10)
            ->withQueryString();

        $statistik = [
            'total' => $tugas->total(),
            'selesai' => $tugas->getCollection()->filter(
                fn($t) => ($t->submissions->first()?->status) === 'completed'
            )->count(),
            'terlambat' => $tugas->getCollection()->filter(
                fn($t) => $t->deadline->isPast() && ($t->submissions->first()?->status ?? 'pending') === 'pending'
            )->count(),
        ];

        return view('mahasiswa.tugas.index', compact('mahasiswa', 'tugas', 'statistik'));
    }

    public function show(Tugas $tugas): View
    {
        $mahasiswa = Auth::user()->mahasiswa;

        abort_unless(
            $tugas->targets()->where('mahasiswa_id', $mahasiswa->id)->exists(),
            403,
            'Tugas ini tidak ditujukan untuk Anda.'
        );

        $tugas->load('dosen');
        $submission = TugasSubmission::where('tugas_id', $tugas->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        return view('mahasiswa.tugas.show', compact('tugas', 'submission', 'mahasiswa'));
    }

    /** Kumpulkan / perbarui tugas. */
    public function submit(Request $request, Tugas $tugas): RedirectResponse
    {
        $mahasiswa = Auth::user()->mahasiswa;

        abort_unless(
            $tugas->targets()->where('mahasiswa_id', $mahasiswa->id)->exists(),
            403,
            'Tugas ini tidak ditujukan untuk Anda.'
        );

        $submission = TugasSubmission::where('tugas_id', $tugas->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if ($submission && in_array($submission->status, ['completed', 'reviewed'], true)) {
            return back()->with('error', 'Tugas ini sudah dinilai dan tidak dapat dikirim ulang.');
        }

        $validated = $request->validate([
            'file' => ['required_without:catatan', 'nullable', 'array', 'max:5'],
            'file.*' => ['file', 'max:5120'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $paths = [];
        if ($request->hasFile('file')) {
            $paths = $this->storage->simpanBanyak(
                $request->file('file'), 'tugas', 'tugas', $mahasiswa->user_id
            );
        }

        $lama = $submission?->file ?? [];

        TugasSubmission::updateOrCreate(
            ['tugas_id' => $tugas->id, 'mahasiswa_id' => $mahasiswa->id],
            [
                'file' => $paths ?: $lama,
                'catatan' => $validated['catatan'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        \App\Models\AuditLog::catat('tugas', 'create', 'Submit tugas "' . $tugas->judul . '" oleh ' . $mahasiswa->nama);

        return back()->with('success', 'Tugas berhasil dikirim ke pembimbing.');
    }
}
