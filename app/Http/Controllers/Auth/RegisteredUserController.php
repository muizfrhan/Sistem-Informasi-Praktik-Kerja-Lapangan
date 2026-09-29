<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Pendaftaran mandiri.
 *
 * DEMEKA KESEBATASAN: akun yang dibuat di sini HANYA berrole `mahasiswa`.
 * Role admin / dosen / koordinator / pimpinan dibuat operator lewat modul
 * master data, tidak pernah dari form publik. Nilai `role` dari request
 * diabaikan sepenuhnya dan dipaksa menjadi `mahasiswa`.
 */
class RegisteredUserController extends Controller
{
    /** Role yang boleh dibuat sendiri lewat form pendaftaran publik. */
    private const SELF_SERVICE_ROLE = 'mahasiswa';

    /**
     * Tampilkan formulir pendaftaran.
     */
    public function create(): View
    {
        return view('auth.register', [
            // Saran isian "pilih atau ketik" — bukan daftar tertutup.
            'jurusanOptions' => Jurusan::where('aktif', true)->orderBy('nama')->pluck('nama'),
            'prodiOptions' => self::nilaiUnik('program_studi'),
            'kelasOptions' => self::kelasUnik(),
        ]);
    }

    /**
     * Nilai unik yang sudah pernah dipakai — sumber daftar saran.
     *
     * @return array<int, string>
     */
    private static function nilaiUnik(string $kolom): array
    {
        return DB::table('mahasiswa')
            ->whereNotNull($kolom)
            ->pluck($kolom)
            ->map(fn ($nilai) => trim((string) $nilai))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Sarana kelas: master `kelas` digabung dengan nilai yang sudah dipakai.
     *
     * @return array<int, string>
     */
    private static function kelasUnik(): array
    {
        return DB::table('kelas')->pluck('nama')
            ->merge(self::nilaiUnik('kelas'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Samakan nama jurusan yang diketik dengan master data.
     *
     * Jurusan yang belum terdaftar otomatis dibuatkan (kode digenerate), supaya
     * pendaftar tidak tersaring hanya karena jurusannya baru.
     */
    private static function resolveJurusan(string $nama): Jurusan
    {
        $nama = trim($nama);

        $jurusan = Jurusan::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->first();
        if ($jurusan) {
            return $jurusan;
        }

        return Jurusan::create(['kode' => self::kodeJurusan($nama), 'nama' => $nama]);
    }

    /** Kode unik dari nama jurusan, mis. "Desain Grafika" -> "DESAINGRAF". */
    private static function kodeJurusan(string $nama): string
    {
        $dasar = Str::upper(Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $nama) ?: 'JURUSAN', 14, ''));
        $kode = $dasar;

        for ($urut = 2; Jurusan::where('kode', $kode)->exists(); $urut++) {
            $kode = $dasar.$urut;
        }

        return $kode;
    }

    /**
     * Simpan akun mahasiswa baru dengan status `ditunda` sampai disetujui operator.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', 'unique:mahasiswa,nim'],
            // users.email = 255, mahasiswa.email = 120 -> pakai yang lebih ketat
            'email' => ['required', 'string', 'lowercase', 'email', 'max:120', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'jurusan' => ['required', 'string', 'max:100'],
            'program_studi' => ['required', 'string', 'max:100'],
            'kelas' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'integer', 'between:1,8'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'setuju' => ['accepted'],
        ], [
            'nim.regex' => 'NIM/NISN hanya boleh berisi angka.',
            'no_hp.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, atau tanda + - ( ).',
            'setuju.accepted' => 'Anda harus menyetujui ketentuan terlebih dahulu.',
        ], [
            'name' => 'nama lengkap',
            'nim' => 'NIM/NISN',
            'no_hp' => 'nomor WhatsApp',
            'program_studi' => 'program studi',
            'setuju' => 'persetujuan ketentuan',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Nama jurusan dari form (pilih atau ketik) dicocokkan ke master data.
            $jurusan = self::resolveJurusan($validated['jurusan']);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'password' => $validated['password'], // cast 'hashed' di model User
                'role' => self::SELF_SERVICE_ROLE, // nilai request sengaja diabaikan
                'role_id' => Role::where('name', self::SELF_SERVICE_ROLE)->value('id'),
                'status' => 'ditunda', // menunggu persetujuan operator
            ]);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nama' => $validated['name'],
                'nim' => $validated['nim'],
                'jurusan_id' => $jurusan->id,
                'program_studi' => $validated['program_studi'],
                'kelas' => $validated['kelas'],
                'semester' => (string) $validated['semester'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'status' => 'aktif',
            ]);

            return $user;
        });

        event(new Registered($user));

        // Langsung masuk supaya pengguna bisa memantau status persetujuannya.
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('akun.ditunda')
            ->with('status', 'Pendaftaran berhasil. Akun Anda menunggu persetujuan operator PKL sekolah.');
    }
}
