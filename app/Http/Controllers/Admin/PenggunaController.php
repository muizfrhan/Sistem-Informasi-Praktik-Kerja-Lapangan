<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Manajemen status akun.
 *
 * Form pendaftaran mandiri membuat user berstatus `ditunda`.
 * Modul ini dipakai operator untuk menyetujui (`aktif`) atau menonaktifkan akun.
 */
class PenggunaController extends Controller
{
    /** Status yang boleh diset dari UI. `ditunda` tidak boleh diset manual. */
    private const SETTABLE_STATUS = ['aktif', 'nonaktif'];

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $users = User::query()
            ->with(['mahasiswa', 'dosen'])
            ->when(in_array($status, ['aktif', 'nonaktif', 'ditunda'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->string('q')->toString().'%';
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('name', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nim', 'like', $keyword));
                });
            })
            ->orderByRaw("FIELD(status, 'ditunda', 'nonaktif', 'aktif')")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengguna.index', [
            'users' => $users,
            'status' => $status,
            'q' => $request->string('q')->toString(),
            'jumlahDitunda' => User::where('status', 'ditunda')->count(),
        ]);
    }

    /**
     * Ubah status akun (setujui / nonaktifkan).
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::SETTABLE_STATUS)],
        ]);

        // Jangan biarkan admin mengunci dirinya sendiri.
        if ($user->is($request->user()) && $validated['status'] !== 'aktif') {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
        }

        $sebelum = $user->status;
        $user->update(['status' => $validated['status']]);

        Log::info('Status akun diubah oleh operator', [
            'aktor' => $request->user()?->email,
            'target' => $user->email,
            'dari' => $sebelum,
            'ke' => $validated['status'],
        ]);

        $pesan = $validated['status'] === 'aktif'
            ? "Akun {$user->email} berhasil diaktifkan."
            : "Akun {$user->email} berhasil dinonaktifkan.";

        return back()->with('success', $pesan);
    }
}
