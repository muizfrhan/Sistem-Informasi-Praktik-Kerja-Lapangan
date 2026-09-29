<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information, including the profile photo.
     *
     * Aturan main foto:
     *   - validasi sudah Dicek di ProfileUpdateRequest (MIME asli + ukuran),
     *   - nama file diacak supaya tidak menebak path milik orang lain,
     *   - file lama dihapus hanya SETELAH file baru berhasil tersimpan,
     *     jadi foto lama tidak hilang kalau upload barunya gagal.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['foto', 'hapus_foto']);

        $user->fill($data);

        // Path foto lama harus diambil SEBELUM kolomnya ditimpa, kalau tidak
        // file lama tidak tahu harus dihapus.
        $fotoLama = $user->foto;

        // Hapus foto: kolom "hapus_foto" berisi "0" atau "1".
        if ($request->boolean('hapus_foto')) {
            $this->hapusBerkas($fotoLama);
            $fotoLama = null;
            $user->forceFill(['foto' => null]);
        }

        if ($request->hasFile('foto')) {
            $path = $this->simpanFoto($request, $user);

            $user->forceFill(['foto' => $path])->save();

            // File lama baru dibuang setelah yang baru benar-benar tersimpan.
            if ($fotoLama && $fotoLama !== $path) {
                $this->hapusBerkas($fotoLama);
            }

            return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil disimpan.');
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $this->hapusFoto($user);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Simpan foto profil baru ke disk `public`.
     *
     * @return string path relatif, mis. "foto-profil/9/8f3c1a2b4d5e.jpg"
     */
    private function simpanFoto(ProfileUpdateRequest $request, $user): string
    {
        $file = $request->file('foto');
        $ext = strtolower($file->getClientOriginalExtension());

        // Sharding per 1000 user supaya satu folder tidak berisi ribuan file.
        $folder = User::FOTO_FOLDER . '/' . (int) floor($user->id / 1000);

        $path = $file->storeAs(
            $folder,
            'foto-' . $user->id . '-' . Str::random(16) . '.' . $ext,
            'public'
        );

        if ($path === false) {
            throw ValidationException::withMessages([
                'foto' => 'Foto gagal disimpan. Periksa izin tulis folder storage.',
            ]);
        }

        return $path;
    }

    /** Buang satu berkas foto dari disk. */
    private function hapusBerkas(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Kosongkan kolom `foto` lalu hapus berkasnya. */
    private function hapusFoto($user): void
    {
        if ($user->foto) {
            $this->hapusBerkas($user->foto);
            $user->forceFill(['foto' => null])->save();
        }
    }
}
