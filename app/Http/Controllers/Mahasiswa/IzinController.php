<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\BaseModuleController;
use App\Models\Izin;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IzinController extends BaseModuleController
{
    public function __construct(protected FileStorageService $storage) {}

    public function index(Request $request): View
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $izin = Izin::where('mahasiswa_id', $mahasiswa->id)
            ->with(['processor'])
            ->orderByDesc('tanggal_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('mahasiswa.izin.index', compact('mahasiswa', 'izin'));
    }

    public function create(): View
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $periode = \App\Models\PeriodePkl::aktif();

        return view('mahasiswa.izin.create', compact('mahasiswa', 'periode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', 'in:izin,sakit,keluarga'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'min:10', 'max:2000'],
            'lampiran' => ['nullable', 'file', 'max:5120'],
        ]);

        $mahasiswa = Auth::user()->mahasiswa;

        // Validasi: tidak boleh ada izin yang bentrok dengan pengajuan aktif
        $bentrok = Izin::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('tanggal_mulai', '<=', $validated['tanggal_selesai'])
            ->whereDate('tanggal_selesai', '>=', $validated['tanggal_mulai'])
            ->exists();

        if ($bentrok) {
            return back()
                ->withInput()
                ->with('error', 'Anda sudah memiliki pengajuan izin pada rentang tanggal tersebut.');
        }

        $lampiran = null;
        if ($request->hasFile('lampiran')) {
            $lampiran = $this->storage->simpan(
                $request->file('lampiran'), 'izin', 'surat-izin', 'izin', $mahasiswa->user_id
            )['path'];
        }

        $izin = Izin::create([
            'mahasiswa_id' => $mahasiswa->id,
            'periode_id' => \App\Models\PeriodePkl::aktif()?->id,
            'jenis' => $validated['jenis'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'alasan' => $validated['alasan'],
            'lampiran' => $lampiran,
            'status' => 'pending',
        ]);

        \App\Models\AuditLog::catat('izin', 'create', 'Pengajuan izin #' . $izin->id . ' oleh ' . $mahasiswa->nama);

        // Kabari pembimbing & admin
        $notif = app(\App\Services\NotifikasiService::class);
        foreach (\App\Models\Dosen::whereIn(
            'id',
            \App\Models\Bimbingan::where('mahasiswa_id', $mahasiswa->id)->distinct()->pluck('dosen_id')
        )->with('user')->get() as $dosen) {
            if ($dosen->user) {
                $notif->kirim(
                    $dosen->user,
                    'approval',
                    'Pengajuan Izin Baru',
                    $mahasiswa->nama . ' mengajukan izin ' . $validated['jenis']
                        . ' ' . \Illuminate\Support\Carbon::parse($validated['tanggal_mulai'])->format('d M Y') . '.',
                    ['izin_id' => $izin->id],
                    route('dosen.izin.index')
                );
            }
        }

        foreach (\App\Models\User::whereIn('role', ['admin', 'koordinator'])->get() as $admin) {
            $notif->kirim(
                $admin,
                'approval',
                'Pengajuan Izin Baru',
                $mahasiswa->nama . ' mengajukan izin ' . $validated['jenis'] . '.',
                ['izin_id' => $izin->id]
            );
        }

        return redirect()
            ->route('mahasiswa.izin.index')
            ->with('success', 'Pengajuan izin berhasil dikirim dan menunggu persetujuan.');
    }

    public function destroy(Izin $izin): RedirectResponse
    {
        $mahasiswa = Auth::user()->mahasiswa;

        abort_unless($izin->mahasiswa_id === $mahasiswa->id, 403, 'Izin ini bukan milik Anda.');

        if ($izin->status === 'approved') {
            return back()->with('error', 'Izin yang sudah disetujui tidak dapat dibatalkan.');
        }

        $this->storage->hapus($izin->lampiran);
        $izin->delete();

        \App\Models\AuditLog::catat('izin', 'delete', 'Pengajuan izin #' . $izin->id . ' dibatalkan');

        return back()->with('success', 'Pengajuan izin berhasil dibatalkan.');
    }
}
