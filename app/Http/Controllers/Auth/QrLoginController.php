<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\QrLoginException;
use App\Http\Controllers\Controller;
use App\Models\QrLoginSession;
use App\Services\QrLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Login QR ala Discord / WhatsApp Web.
 *
 * Peran dalam alur ini:
 *   - Device B (belum login): `/login` -> tab QR -> membuat permintaan, menampilkan
 *     QR, lalu menunggu status (`status`), lalu mengklaim sesi (`claim`).
 *   - Device A (sudah login): memindai QR -> `/qr-login/{token}` -> konfirmasi
 *     -> menyetujui (`approve`) atau menolak (`reject`).
 *
 * Yang masuk ke sistem adalah user yang MENGAZINKAN di Device A-nya sendiri,
 * sehingga role & permission tidak pernah ditentukan oleh QR — dashboard
 * selalu dipilih dari user terautentikasi lewat route `redirect`.
 */
class QrLoginController extends Controller
{
    public function __construct(private readonly QrLoginService $qr) {}

    // ==================================================================
    // Device B — belum login
    // ==================================================================

    /**
     * Buat permintaan login QR dari DASHBOARD (perangkat yang sudah login).
     *
     * Di sini QR-nya milik user yang sudah masuk: ia yang membuat & menampilkan
     * QR, lalu menyalin tautannya untuk dikirim ke perangkat baru. Perangkat
     * baru yang membuka tautan itu akan menunggu persetujuan sebelum masuk.
     *
     * Token mentah hanya dikembalikan di sini (pergi ke gambar QR di memori
     * browser); database hanya menyimpan SHA-256-nya.
     */
    public function issue(Request $request): JsonResponse
    {
        abort_if(! $request->user()?->isAktif(), 403);

        ['record' => $record, 'token' => $token] = $this->qr->createRequest($request);

        return response()->json([
            'token' => $token,
            'url' => route('qr.login.authorize', ['token' => $token]),
            'status' => $record->status,
            'expires_in' => $record->sisaDetik(),
        ]);
    }

    /**
     * Status terkini untuk polling Device B.
     */
    public function status(Request $request): JsonResponse
    {
        $record = $this->recordFromSession($request);

        if (! $record) {
            return response()->json(['status' => 'none', 'message' => 'Belum ada QR aktif.']);
        }

        return response()->json($this->qr->clientState($record->fresh() ?? $record));
    }

    /**
     * Device B mengambil sesi login setelah QR disetujui.
     */
    public function claim(Request $request): RedirectResponse
    {
        $record = $this->recordFromSession($request);

        if (! $record) {
            return redirect()->route('login')->with('swal_error', 'Sesi login QR tidak ditemukan.');
        }

        try {
            ['user' => $user] = $this->qr->claim($record, $request);
        } catch (QrLoginException $e) {
            $request->session()->forget('qr_login_id');

            return redirect()->route('login')->with('swal_error', $e->getMessage());
        }

        // Buang request-nya supaya token tidak bisa dipakai ulang dari tab ini.
        $request->session()->forget('qr_login_id');

        // Autentikasi = akun yang mengotorisasi di perangkatnya sendiri.
        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        // Akun nonaktif/ditunda tetap boleh masuk, tapi hanya ke halaman status.
        if (! $user->isAktif()) {
            return redirect()->route('akun.ditunda');
        }

        return redirect()->route('redirect');
    }

    // ==================================================================
    // Device A — sudah login
    // ==================================================================

    /**
     * Halaman `/qr-login/{token}` untuk kedua sisi.
     *
     * - Sudah login  -> halaman konfirmasi (pengguna menyetujui / menolak).
     * - Belum login  -> perangkat baru mengikat permintaan ke sesinya lalu
     *   menunggu; begitu diizinkan, sesi login dibuat otomatis.
     *
     * Membuka tautan inilah yang memicu notifikasi di perangkat pemilik,
     * jadi identitas perangkat peminta dicatat di sini — bukan saat QR dibuat.
     */
    public function authorize(Request $request, string $token): View
    {
        $sudahLogin = (bool) $request->user();

        try {
            $record = $this->qr->findScannable($token);
        } catch (QrLoginException $e) {
            return view($sudahLogin ? 'qr.authorize' : 'qr.wait', [
                'record' => null,
                'token' => $token,
                'problem' => $e->reason,
                'message' => $this->problemMessage($e->reason),
            ]);
        }

        // Pembuka tautan inilah yang tercatat sebagai perangkat peminta.
        $record = $this->qr->markPickedUp($record, $request);

        // Perangkat baru: ikat ke sesi browser ini (satu token = satu perangkat).
        if (! $sudahLogin) {
            $request->session()->put('qr_login_id', $record->id);

            return view('qr.wait', [
                'record' => $record->fresh(),
                'token' => $token,
                'problem' => null,
                'message' => null,
                'lokasi' => $this->qr->lokasiSingkat($record->fresh()->ip),
            ]);
        }

        $this->qr->markAwaitingConfirmation($record);

        return view('qr.authorize', [
            'record' => $record->fresh(),
            'token' => $token,
            'problem' => null,
            'message' => null,
            'lokasi' => $this->qr->lokasiSingkat($record->fresh()->ip),
        ]);
    }

    /** User menyetujui: izinkan Device B masuk dengan akun ini. */
    public function approve(Request $request, string $token): RedirectResponse
    {
        try {
            $record = $this->qr->findScannable($token);
            $this->qr->approve($record, $request->user());
        } catch (QrLoginException $e) {
            return redirect()
                ->route('qr.login.authorize', ['token' => $token])
                ->with('swal_error', $this->problemMessage($e->reason));
        }

        return redirect()
            ->route('perangkat')
            ->with('status', 'Perangkat diizinkan. Periksa perangkat baru Anda — login sudah diproses.');
    }

    /** User menolak. */
    public function reject(Request $request, string $token): RedirectResponse
    {
        try {
            $record = $this->qr->findScannable($token);
            $this->qr->reject($record);
        } catch (QrLoginException $e) {
            return redirect()
                ->route('qr.login.authorize', ['token' => $token])
                ->with('swal_error', $this->problemMessage($e->reason));
        }

        return redirect()
            ->route('perangkat')
            ->with('status', 'Permintaan login ditolak. Perangkat baru tidak akan masuk.');
    }

    // ==================================================================
    // Dashboard (Device A) — konfirmasi otomatis tanpa pindai kamera
    // ==================================================================

    /**
     * Permintaan QR milik user ini yang sudah dibuka perangkat baru.
     *
     * Dipakai dashboard untuk memunculkan dialog persetujuan di atas
     * halaman sesuai peran user, lalu user memilih Izinkan / Tolak di sana.
     * QR yang baru dibuat (belum ada yang membuka tautannya) tidak muncul di
     * sini — notifikasi hanya menyusul setelah tautan benar-benar dibuka.
     */
    public function pending(Request $request): JsonResponse
    {
        abort_if(! $request->user()?->isAktif(), 403);

        $record = $this->qr->pending($request->user());

        if (! $record) {
            return response()->json(['status' => 'none']);
        }

        return response()->json([
            'id' => $record->id,
            'status' => $record->status,
            'device_name' => $record->device_name,
            'browser' => $record->browser,
            'platform' => $record->platform,
            'lokasi' => $this->qr->lokasiSingkat($record->ip),
            'expires_in' => $record->sisaDetik(),
        ]);
    }

    /**
     * Keputusan dari dashboard (berdasarkan id permintaan, bukan token).
     *
     * Dipakai ketika tautan QR dibuka/ditempel manual sehingga user tidak
     * perlu memindai QR untuk sampai ke halaman konfirmasi.
     */
    public function decide(Request $request, int $id, string $aksi): JsonResponse
    {
        abort_if(! $request->user()?->isAktif(), 403);
        abort_unless(in_array($aksi, ['approve', 'reject'], true), 404);

        // Hanya pemilik QR yang boleh memutuskan permintaannya sendiri.
        $record = QrLoginSession::where('id', $id)
            ->where('owner_user_id', $request->user()->id)
            ->first();

        if (! $record) {
            return response()->json(['ok' => false, 'message' => 'Permintaan tidak ditemukan.'], 404);
        }

        try {
            $this->qr->markAwaitingConfirmation($record);

            $aksi === 'approve'
                ? $this->qr->approve($record, $request->user())
                : $this->qr->reject($record);
        } catch (QrLoginException $e) {
            return response()->json(['ok' => false, 'message' => $this->problemMessage($e->reason)], 409);
        }

        return response()->json([
            'ok' => true,
            'message' => $aksi === 'approve'
                ? 'Perangkat diizinkan. Perangkat baru sudah masuk ke akun ini.'
                : 'Permintaan ditolak.',
        ]);
    }

    // ==================================================================
    // Perangkat Saya
    // ==================================================================

    /**
     * Daftar perangkat yang sedang login ke akun ini.
     *
     * Memakai tabel `sessions` milik Laravel (driver database) sehingga tidak
     * perlu tabel perangkat tambahan. Halaman yang sama juga jadi tempat
     * memindai QR (Device A).
     */
    public function devices(Request $request): View
    {
        $rows = $this->deviceRows($request);
        $driver = (string) config('session.driver');

        return view('qr.devices', [
            'devices' => $rows,
            'sessionId' => $request->session()->getId(),
            'driver' => $driver,
        ]);
    }

    /** Keluar dari satu perangkat (menghapus sesinya). */
    public function destroyDevice(Request $request, string $device): RedirectResponse
    {
        abort_unless(config('session.driver') === 'database', 404);

        $table = (string) config('session.table', 'sessions');
        $row = DB::table($table)
            ->where('id', $device)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $row) {
            return back()->with('swal_error', 'Sesi perangkat tidak ditemukan.');
        }

        DB::table($table)->where('id', $device)->delete();

        if ($device === $request->session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Anda telah keluar dari perangkat ini.');
        }

        return back()->with('status', 'Perangkat berhasil dikeluarkan.');
    }

    /** Keluar dari semua perangkat (termasuk yang sedang dipakai). */
    public function destroyAllDevices(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->id)
                ->delete();
        }

        return redirect()->route('login')->with('status', 'Anda telah keluar dari semua perangkat.');
    }

    // ==================================================================
    // Helper
    // ==================================================================

    /** Permintaan QR yang terikat dengan sesi browser ini. */
    private function recordFromSession(Request $request): ?QrLoginSession
    {
        $id = $request->session()->get('qr_login_id');

        if (! $id) {
            return null;
        }

        $record = QrLoginSession::find($id);

        if (! $record) {
            $request->session()->forget('qr_login_id');

            return null;
        }

        return $record;
    }

    /**
     * Sesi aktif milik user, diparse jadi data perangkat yang terbaca.
     *
     * @return array<int, array{id: string, current: bool, device_name: string, browser: string, platform: string, ip: string, lokasi: string, last_active: Carbon}>
     */
    private function deviceRows(Request $request): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        $lifetime = (int) config('session.lifetime', 120);
        $sessionId = $request->session()->getId();
        $table = (string) config('session.table', 'sessions');

        return DB::table($table)
            ->where('user_id', $request->user()->id)
            ->where('last_activity', '>=', now()->subMinutes($lifetime)->getTimestamp())
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(function ($row) use ($sessionId) {
                $info = $this->qr->deviceInfo($row->user_agent);

                return [
                    'id' => $row->id,
                    'current' => $row->id === $sessionId,
                    'device_name' => $info['device_name'],
                    'browser' => $info['browser'],
                    'platform' => $info['platform'],
                    'ip' => $row->ip_address,
                    'lokasi' => $this->qr->lokasiSingkat($row->ip_address),
                    'last_active' => Carbon::createFromTimestamp($row->last_activity),
                ];
            })
            ->all();
    }

    /** Pesan ramah untuk setiap kondisi QR. */
    private function problemMessage(string $reason): string
    {
        return match ($reason) {
            QrLoginException::EXPIRED => 'QR Code telah kedaluwarsa. Silakan buat QR baru di perangkat itu.',
            QrLoginException::USED => 'QR Code sudah digunakan.',
            QrLoginException::REJECTED => 'Login QR ditolak.',
            QrLoginException::NOT_APPROVED => 'Permintaan ini belum disetujui.',
            QrLoginException::NO_APPROVER => 'Akun pengotorisasi tidak ditemukan.',
            default => 'QR Code tidak valid.',
        };
    }
}
