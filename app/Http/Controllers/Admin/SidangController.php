<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\Mahasiswa;
use App\Models\Sidang;
use App\Models\SidangPenguji;
use App\Models\SidangPeserta;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidangController extends BaseModuleController
{
    public function __construct(protected NotifikasiService $notif) {}

    public function index(Request $request): View
    {
        $sidang = Sidang::with(['periode', 'penguji'])
            ->withCount(['peserta'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sidang.index', compact('sidang'));
    }

    public function create(): View
    {
        return view('admin.sidang.create', [
            'periode' => \App\Models\PeriodePkl::aktif(),
            'mahasiswa' => Mahasiswa::orderBy('nama')->get(['id', 'nama', 'nim']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'ruangan' => ['required', 'string', 'max:80'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $sidang = Sidang::create($validated + [
            'periode_id' => \App\Models\PeriodePkl::aktif()?->id,
            'status' => 'jadwal',
        ]);

        \App\Models\AuditLog::catat('sidang', 'create', 'Sidang "' . $sidang->judul . '" dijadwalkan');

        return redirect()
            ->route('admin.sidang.index')
            ->with('success', 'Jadwal sidang berhasil dibuat.');
    }

    public function show(Sidang $sidang): View
    {
        $sidang->load(['periode', 'penguji', 'peserta.mahasiswa']);

        return view('admin.sidang.show', compact('sidang'));
    }

    public function edit(Sidang $sidang): View
    {
        return view('admin.sidang.edit', compact('sidang'));
    }

    public function update(Request $request, Sidang $sidang): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'ruangan' => ['required', 'string', 'max:80'],
            'status' => ['required', 'in:jadwal,berlangsung,selesai,batal'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $sidang->update($validated);

        \App\Models\AuditLog::catat('sidang', 'update', 'Sidang #' . $sidang->id . ' diperbarui');

        return back()->with('success', 'Data sidang berhasil diperbarui.');
    }

    public function destroy(Sidang $sidang): RedirectResponse
    {
        if ($sidang->status === 'selesai') {
            return back()->with('error', 'Sidang yang sudah selesai tidak dapat dihapus.');
        }

        \App\Models\AuditLog::catat('sidang', 'delete', 'Sidang #' . $sidang->id . ' dihapus');
        $sidang->delete();

        return back()->with('success', 'Sidang berhasil dihapus.');
    }

    public function tambahPeserta(Request $request, Sidang $sidang): RedirectResponse
    {
        $validated = $request->validate([
            'mahasiswa_id' => ['required', 'array', 'min:1'],
            'mahasiswa_id.*' => ['exists:mahasiswa,id'],
        ]);

        foreach ($validated['mahasiswa_id'] as $mhsId) {
            SidangPeserta::firstOrCreate(
                ['sidang_id' => $sidang->id, 'mahasiswa_id' => $mhsId]
            );
        }

        \App\Models\AuditLog::catat('sidang', 'create', count($validated['mahasiswa_id']) . ' peserta ditambahkan ke #' . $sidang->id);

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function hapusPeserta(Sidang $sidang, SidangPeserta $peserta): RedirectResponse
    {
        abort_unless($peserta->sidang_id === $sidang->id, 403);

        $peserta->delete();

        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function tambahPenguji(Request $request, Sidang $sidang): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'nama' => ['required_without:user_id', 'nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
        ]);

        $nama = $validated['nama'] ?: \App\Models\User::find($validated['user_id'] ?? 0)?->name;

        if (! $nama) {
            return back()->with('error', 'Nama penguji wajib diisi.');
        }

        SidangPenguji::create([
            'sidang_id' => $sidang->id,
            'user_id' => $validated['user_id'] ?? null,
            'nama' => $nama,
            'jabatan' => $validated['jabatan'] ?? 'Penguji',
        ]);

        return back()->with('success', 'Penguji berhasil ditambahkan.');
    }

    public function nilai(Request $request, Sidang $sidang): RedirectResponse
    {
        $validated = $request->validate([
            'nilai_presentasi' => ['nullable', 'array'],
            'nilai_presentasi.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:1000'],
            'feedback' => ['nullable', 'array'],
            'feedback.*' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($sidang->penguji as $penguji) {
            $penguji->update([
                'nilai_presentasi' => $validated['nilai_presentasi'][$penguji->id] ?? null,
                'catatan' => $validated['catatan'][$penguji->id] ?? null,
                'feedback' => $validated['feedback'][$penguji->id] ?? null,
            ]);
        }

        \App\Models\AuditLog::catat('sidang', 'approve', 'Nilai presentasi #' . $sidang->id . ' diisi');

        return back()->with('success', 'Nilai presentasi berhasil disimpan.');
    }
}
