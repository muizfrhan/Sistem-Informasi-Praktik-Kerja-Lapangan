<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\Mahasiswa;
use App\Models\PenilaianKategori;
use App\Models\PenilaianKomponen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenilaianKategoriController extends BaseModuleController
{
    public function index(): View
    {
        $kategori = PenilaianKategori::withCount('detail')->orderBy('urutan')->get();
        $komponen = PenilaianKomponen::orderBy('kode')->get();

        $totalBobotKategori = (float) $kategori->where('aktif', true)->sum('bobot');
        $totalBobotKomponen = (float) $komponen->where('aktif', true)->sum('bobot');

        return view('admin.penilaian.index', compact(
            'kategori', 'komponen', 'totalBobotKategori', 'totalBobotKomponen'
        ));
    }

    public function create(): View
    {
        return view('admin.penilaian.create', ['kategori' => new PenilaianKategori]);
    }

    public function store(Request $request): RedirectResponse
    {
        PenilaianKategori::create($this->validasi($request));

        \App\Models\AuditLog::catat('penilaian', 'create', 'Kategori penilaian baru: ' . $request->string('nama'));

        return redirect()
            ->route('admin.kategori-penilaian.index')
            ->with('success', 'Kategori penilaian berhasil ditambahkan.');
    }

    public function edit(PenilaianKategori $kategori_penilaian): View
    {
        return view('admin.penilaian.edit', ['kategori' => $kategori_penilaian]);
    }

    public function update(Request $request, PenilaianKategori $kategori_penilaian): RedirectResponse
    {
        $kategori_penilaian->update($this->validasi($request));

        \App\Models\AuditLog::catat('penilaian', 'update', 'Kategori penilaian #' . $kategori_penilaian->id . ' diperbarui');

        return redirect()
            ->route('admin.kategori-penilaian.index')
            ->with('success', 'Kategori penilaian berhasil diperbarui.');
    }

    public function destroy(PenilaianKategori $kategori_penilaian): RedirectResponse
    {
        if ($kategori_penilaian->detail()->exists()) {
            return back()->with('error', 'Kategori yang sudah dipakai pada penilaian tidak dapat dihapus. Nonaktifkan saja.');
        }

        \App\Models\AuditLog::catat('penilaian', 'delete', 'Kategori penilaian #' . $kategori_penilaian->id . ' dihapus');
        $kategori_penilaian->delete();

        return back()->with('success', 'Kategori penilaian berhasil dihapus.');
    }

    /** Simpan bobot komponen penilaian sekaligus. */
    public function updateKomponen(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bobot' => ['required', 'array'],
            'bobot.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['bobot'] as $id => $bobot) {
                PenilaianKomponen::whereKey($id)->update(['bobot' => $bobot]);
            }
        });

        $total = PenilaianKomponen::where('aktif', true)->sum('bobot');
        $pesan = $total == 100
            ? 'Bobot komponen penilaian berhasil disimpan.'
            : "Bobot komponen tersimpan. Peringatan: total bobot saat ini {$total}%, sebaiknya 100%.";

        \App\Models\AuditLog::catat('penilaian', 'update', 'Bobot komponen penilaian diperbarui');

        return back()->with($total == 100 ? 'success' : 'error', $pesan);
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/', 'unique:penilaian_kategori,kode,' . ($request->route('kategori_penilaian')?->id ?? '')],
            'nama' => ['required', 'string', 'max:80'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
            'aktif' => ['nullable', 'boolean'],
        ]);
    }
}
