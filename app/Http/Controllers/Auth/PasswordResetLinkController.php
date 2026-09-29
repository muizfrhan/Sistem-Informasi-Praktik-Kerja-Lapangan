<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan form "Lupa Kata Sandi".
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset ke email yang diberikan.
     *
     * Selalu balas sukses supaya halaman ini tidak bisa dipakai menebak
     * email mana saja yang terdaftar di sistem.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [], [
            'email' => 'email terdaftar',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => 'Terlalu banyak permintaan. Silakan coba lagi dalam beberapa menit.',
            ]);
        }

        // STATUS_INVALID sengaja tidak ditampilkan agar email tidak bisa ditebak.
        // Pesan ditulis langsung karena project ini tidak punya folder lang.
        return back()->with('success', 'Jika email tersebut terdaftar, tautan atur ulang kata sandi sudah dikirim.');
    }
}
