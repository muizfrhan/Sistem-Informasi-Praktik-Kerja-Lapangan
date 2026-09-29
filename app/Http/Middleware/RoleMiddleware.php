<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses berdasarkan role user.
 *
 * Contoh pemakaian pada route:
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,koordinator')   // salah satu dari
 *   ->middleware('role:dosen,pembimbing_perusahaan')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if ($user && $user->status === 'aktif' && in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Role Anda: ' . ($user?->role ?? 'tidak dikenal') . '.');
    }
}
