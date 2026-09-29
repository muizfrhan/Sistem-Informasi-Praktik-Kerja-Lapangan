<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseModuleController;
use App\Models\PeriodePkl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PeriodeController extends BaseModuleController
{
    public function index(Request $request): View
    {
        $periode = PeriodePkl::withCount(['pendaftaran', 'absensi', 'jurnal', 'sertifikat'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('tanggal_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('admin.periode.index', compact('periode'));
    }

    public function create(): View
    {
        return view('admin.periode.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        PeriodePkl::create($data + ['created_by' => auth()->id()]);

        \App\Models\AuditLog::catat('periode', 'create', 'Periode ' . $data['kode'] . ' dibuat');

        return redirect()
            ->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil dibuat.');
    }

    public function edit(PeriodePkl $periode): View
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, PeriodePkl $periode): RedirectResponse
    {
        $periode->update($this->validasi($request, $periode));

        \App\Models\AuditLog::catat('periode', 'update', 'Periode ' . $periode->kode . ' diperbarui');

        return redirect()
            ->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil diperbarui.');
    }

    public function destroy(PeriodePkl $periode): RedirectResponse
    {
        if ($periode->absensi()->exists() || $periode->jurnal()->exists()) {
            return back()->with('error', 'Periode yang sudah memiliki data absensi atau jurnal tidak dapat dihapus.');
        }

        \App\Models\AuditLog::catat('periode', 'delete', 'Periode ' . $periode->kode . ' dihapus');
        $periode->delete();

        return back()->with('success', 'Periode PKL berhasil dihapus.');
    }

    private function validasi(Request $request, ?PeriodePkl $periode = null): array
    {
        return $request->validate([
            'kode' => [
                'required', 'string', 'max:30',
                Rule::unique('periode_pkl', 'kode')->ignore($periode?->id),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'status' => ['required', 'in:draft,pendaftaran,penempatan,pelaksanaan,evaluasi,selesai'],
            'batas_pendaftaran' => ['nullable', 'date', 'before_or_equal:tanggal_selesai'],
            'batas_laporan' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'kuota_total' => ['nullable', 'integer', 'min:1'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
