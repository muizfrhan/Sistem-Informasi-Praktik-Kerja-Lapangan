<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Chat internal berbasis polling (bukan realtime) agar tidak menambah
 * infrastruktur websocket. Aman untuk arsitektur existing.
 */
class PercakapanController extends Controller
{
    public function index(): View
    {
        $percakapan = Percakapan::whereHas('peserta', fn($q) => $q->where('users.id', Auth::id()))
            ->with(['peserta', 'mahasiswa', 'pesan' => fn($q) => $q->latest('id')->limit(1)])
            ->withCount('pesan')
            ->orderByDesc('pesan_terakhir_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('chat.index', compact('percakapan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'konteks' => ['required', 'in:umum,bimbingan,laporan,absensi'],
            'judul' => ['nullable', 'string', 'max:150'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $teman = User::findOrFail($validated['user_id']);
        $konteks = $validated['konteks'];

        // Cegah duplikat percakapan 1:1 dengan orang yang sama + konteks sama
        $ada = Percakapan::where('konteks', $konteks)
            ->whereHas('peserta', fn($q) => $q->where('users.id', Auth::id()))
            ->whereHas('peserta', fn($q) => $q->where('users.id', $teman->id))
            ->withCount('peserta')
            ->get()
            ->first(fn($p) => $p->peserta_count === 2);

        if ($ada) {
            return redirect()->route('chat.show', $ada);
        }

        $percakapan = Percakapan::create([
            'konteks' => $konteks,
            'judul' => $validated['judul'] ?: $teman->name,
            'mahasiswa_id' => $this->idMahasiswa(),
        ]);

        $percakapan->peserta()->syncWithoutDetaching([Auth::id(), $teman->id]);

        return redirect()->route('chat.show', $percakapan)
            ->with('success', 'Percakapan dengan ' . $teman->name . ' dimulai.');
    }

    public function show(Percakapan $percakapan): View
    {
        $this->pastikanPeserta($percakapan);

        $percakapan->load('peserta');

        $pesan = $percakapan->pesan()
            ->with('user')
            ->orderBy('id')
            ->paginate(50);

        // Tandai semua pesan sudah dibaca
        $percakapan->pesan()
            ->whereNull('dibaca_at')
            ->where('user_id', '!=', Auth::id())
            ->update(['dibaca_at' => now()]);

        return view('chat.show', compact('percakapan', 'pesan'));
    }

    /** Endpoint polling: ambil pesan baru. */
    public function pesan(Request $request, Percakapan $percakapan): JsonResponse
    {
        $this->pastikanPeserta($percakapan);

        $sejak = $request->integer('sejak', 0);

        $pesan = $percakapan->pesan()
            ->with('user:id,name,role')
            ->where('id', '>', $sejak)
            ->orderBy('id')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'isi' => e($p->isi),
                'saya' => $p->user_id === Auth::id(),
                'nama' => $p->user?->name,
                'waktu' => $p->created_at->format('H:i'),
            ]);

        return response()->json(['pesan' => $pesan]);
    }

    public function kirim(Request $request, Percakapan $percakapan): RedirectResponse
    {
        $this->pastikanPeserta($percakapan);

        $validated = $request->validate([
            'isi' => ['required', 'string', 'max:2000'],
        ]);

        Pesan::create([
            'percakapan_id' => $percakapan->id,
            'user_id' => Auth::id(),
            'isi' => $validated['isi'],
        ]);

        $percakapan->update(['pesan_terakhir_at' => now()]);

        return back()->with('success', 'Pesan terkirim.');
    }

    public function tandaiDibaca(Percakapan $percakapan): RedirectResponse
    {
        $this->pastikanPeserta($percakapan);

        $percakapan->pesan()
            ->whereNull('dibaca_at')
            ->where('user_id', '!=', Auth::id())
            ->update(['dibaca_at' => now()]);

        $percakapan->peserta()->updateExistingPivot(Auth::id(), ['terakhir_dibaca' => now()]);

        return back();
    }

    /** Orang yang bisa memulai percakapan dengan user saat ini. */
    public function kontak(): JsonResponse
    {
        $user = Auth::user();

        $ids = match ($user->role) {
            'mahasiswa' => Dosen::whereIn('id', \App\Models\Bimbingan::where('mahasiswa_id', $user->mahasiswa?->id)
                ->distinct()->pluck('dosen_id'))->pluck('user_id'),
            'dosen' => \App\Models\Mahasiswa::whereIn('id', \App\Models\Bimbingan::where('dosen_id', $user->dosen?->id)
                ->distinct()->pluck('mahasiswa_id'))->pluck('user_id'),
            default => User::whereIn('role', ['admin', 'koordinator', 'dosen'])->pluck('id'),
        };

        $orang = User::whereIn('id', $ids)
            ->where('id', '!=', $user->id)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return response()->json($orang);
    }

    private function pastikanPeserta(Percakapan $percakapan): void
    {
        abort_unless(
            $percakapan->peserta()->where('users.id', Auth::id())->exists(),
            403,
            'Anda bukan peserta percakapan ini.'
        );
    }

    private function idMahasiswa(): ?int
    {
        return Auth::user()->mahasiswa?->id;
    }
}
