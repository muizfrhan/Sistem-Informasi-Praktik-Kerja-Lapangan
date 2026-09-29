<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notification center + pengumuman. Tersedia untuk semua role.
 */
class NotifikasiController extends Controller
{
    public function __construct(protected NotifikasiService $service) {}

    public function index(Request $request): View
    {
        $notifikasi = Notifikasi::untuk($request->user())
            ->when($request->filled('tipe'), fn($q) => $q->where('tipe', $request->string('tipe')))
            ->when($request->boolean('belum'), fn($q) => $q->belumDibaca())
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $pengumuman = Pengumuman::terbit()
            ->untukRole($request->user()->role)
            ->with('penulis')
            ->orderByDesc('pin')
            ->orderByDesc('published_at')
            ->paginate(5);

        return view('notifikasi.index', compact('notifikasi', 'pengumuman'));
    }

    public function tandaiDibaca(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403);

        $this->service->tandaiDibaca($notifikasi);

        return back();
    }

    public function tandaiSemuaDibaca(Request $request): RedirectResponse
    {
        $jumlah = $this->service->tandaiSemuaDibaca($request->user());

        return back()->with('success', "{$jumlah} notifikasi ditandai sudah dibaca.");
    }

    /** Endpoint polling untuk badge navbar. */
    public function badge(Request $request)
    {
        return response()->json([
            'belum_dibaca' => $this->service->unreadCount($request->user()),
        ]);
    }
}
