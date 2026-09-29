<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman status akun untuk user yang belum/dis menyatakan tidak aktif.
 *
 * Dipakai setelah pendaftaran mandiri (status `ditunda`) maupun akun yang
 * dinonaktifkan operator. User sudah login di sini, tapi RoleMiddleware
 * akan menolak semua modul sampai statusnya `aktif`.
 */
class AccountStatusController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Sudah aktif? Tidak ada yang perlu dilihat di halaman ini.
        if ($user->isAktif()) {
            return redirect()->route('redirect');
        }

        return view('auth.account-status', [
            'user' => $user,
            'alasan' => $user->status === 'ditunda'
                ? 'Akun Anda sedang menunggu persetujuan operator PKL sekolah.'
                : 'Akun Anda berstatus nonaktif. Hubungi administrator untuk mengaktifkan kembali.',
        ]);
    }
}
