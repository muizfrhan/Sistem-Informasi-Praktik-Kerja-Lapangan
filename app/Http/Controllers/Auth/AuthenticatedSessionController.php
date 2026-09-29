<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /** Maksimal percobaan login gagal sebelum dikunci sementara. */
    private const MAX_ATTEMPTS = 5;

    /** Lama penguncian dalam detik. */
    private const DECAY_SECONDS = 60;

    /**
     * Tampilkan form login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login.
     *
     * Identifier tunggal `login` dicocokkan ke tiga sumber data:
     *   - NIM  -> tabel `mahasiswa` (role mahasiswa)
     *   - NIP  -> tabel `dosen`     (role dosen / koordinator / pimpinan)
     *   - email-> tabel `users`     (seluruh role berbasis akun)
     */
    public function store(Request $request): RedirectResponse
    {
        // Identitas bisa berupa angka (NIM/NIP). Normalkan ke string & trim
        // supaya klien yang mengirim angka atau spasi berlebih tetap aman.
        $request->merge(['login' => trim((string) $request->input('login'))]);

        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'login.required' => 'Kolom identitas wajib diisi.',
            'login.max' => 'Identitas yang dimasukkan terlalu panjang.',
            'password.required' => 'Kata sandi wajib diisi.',
        ], [
            'login' => 'identitas',
            'password' => 'kata sandi',
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->status(429);
        }

        $user = $this->findUserByIdentifier($credentials['login']);

        // Pesan sengaja disamakan untuk identitas tidak dikenal maupun sandi salah,
        // supaya halaman ini tidak bisa dipakai menebak identitas yang terdaftar.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'login' => 'Login gagal. Periksa kembali identitas dan kata sandi Anda.',
            ]);
        }

        $rememberMe = (bool) ($credentials['remember'] ?? false);

        // Akun nonaktif / ditunda tetap boleh masuk, tetapi hanya diarahkan
        // ke halaman status — seluruh modul dilindungi RoleMiddleware.
        if (! $user->isAktif()) {
            Auth::login($user, $rememberMe);
            $request->session()->regenerate();

            return redirect()->route('akun.ditunda');
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $rememberMe);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        // PENTING: langsung ke dasbor peran, tidak lewat route `redirect`.
        // Flash message hanya bertahan satu request; kalau login ->
        // /redirect -> dasbor, pesan "selamat datang" akan hilang di tengah.
        return redirect()->intended(route($this->namaRouteDasbor($user)))
            ->with('success', 'Selamat datang kembali, ' . $user->name . '.');
    }

    /**
     * Rute `/redirect`: mengarahkan ke dasbor sesuai peran.
     *
     * Dipakai sebagai fallback (mis. setelah ganti peran di akun demo).
     * Login sendiri langsung menuju dasbor agar flash message tidak hilang.
     */
    public function redirectKeDasbor(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAktif()) {
            return redirect()->route('akun.ditunda');
        }

        $nama = $this->namaRouteDasbor($user);

        // Dasbor peran ini belum dibuat -> kirim ke beranda, jangan 500.
        if ($nama === 'landing' || ! view()->exists($view = $this->namaViewDasbor($user))) {
            return redirect()->route('landing')->with(
                'error',
                'Dasbor untuk peran ' . $user->labelRole() . ' belum tersedia. Hubungi administrator.'
            );
        }

        return redirect()->route($nama);
    }

    /**
     * Nama route dasbor sesuai peran.
     *
     * Satu-satunya sumber kebenaran; dipakai login dan route `redirect`.
     */
    public function namaRouteDasbor(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'dosen' => 'dosen.dashboard',
            'mahasiswa' => 'mahasiswa.dashboard',
            'koordinator' => 'koordinator.dashboard',
            'pimpinan' => 'pimpinan.dashboard',
            default => 'landing',
        };
    }

    /** Nama view yang dirender oleh route dasbor (untuk pengecekan tersedia/tidak). */
    private function namaViewDasbor(User $user): ?string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'dosen' => 'dosen.dashboard',
            'mahasiswa' => 'mahasiswa.dashboard',
            'koordinator' => 'koordinator.dashboard',
            'pimpinan' => 'pimpinan.dashboard',
            default => null,
        };
    }

    /**
     * Logout user dan akhiri sesi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('success', 'Kamu telah keluar dari akun.');
    }

    /**
     * Cari user berdasarkan NIM, NIP, atau email.
     */
    private function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);

        // NIM (khusus angka, >= 6 digit)
        if (preg_match('/^\d{6,20}$/', $identifier)) {
            $user = Mahasiswa::where('nim', $identifier)->first()?->user;
            if ($user) {
                return $user;
            }
        }

        // NIP (khusus angka, >= 9 digit)
        if (preg_match('/^\d{9,20}$/', $identifier)) {
            $user = Dosen::where('nip', $identifier)->first()?->user;
            if ($user) {
                return $user;
            }
        }

        // Email (harus mengandung "@")
        if (str_contains($identifier, '@')) {
            return User::where('email', $identifier)->first();
        }

        // Fallback: mungkin admin/NIP tersimpan sebagai username tanpa "@"
        return User::where('username', $identifier)->first();
    }

    /**
     * Kunci rate limit per kombinasi identitas + alamat IP.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->string('login')->toString()).'|'.$request->ip()
        );
    }
}
