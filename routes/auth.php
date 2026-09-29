<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\QrLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // ---------- Masuk ----------
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ---------- Login QR (perangkat baru) ----------
    // Polling status & klaim sesi. Terikat ke sesi browser yang membuka
    // tautan QR, jadi token tidak perlu dikirim ulang.
    Route::post('login/qr/status', [QrLoginController::class, 'status'])
        ->middleware('throttle:'.config('qr-login.throttle.status').',1')
        ->name('qr.login.status');

    Route::post('login/qr/claim', [QrLoginController::class, 'claim'])
        ->middleware('throttle:'.config('qr-login.throttle.claim').',1')
        ->name('qr.login.claim');

    // ---------- Daftar mandiri (khusus mahasiswa) ----------
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // ---------- Lupa kata sandi ----------
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Login QR — halaman & pembuatan
|--------------------------------------------------------------------------
| Di luar middleware auth: halaman yang sama melayani dua pihak.
| Sudah login  -> konfirmasi Izinkan / Tolak.
| Belum login  -> perangkat baru menunggu persetujuan, lalu masuk otomatis.
| Pembuatan QR hanya untuk user login (dari dashboard per role).
*/
Route::get('qr-login/{token}', [QrLoginController::class, 'authorize'])
    ->where('token', '[A-Za-z0-9]{32,128}')
    ->name('qr.login.authorize');

Route::middleware('auth')->group(function () {
    // Halaman status akun yang belum disetujui operator / dinonaktifkan.
    Route::get('akun/ditunda', \App\Http\Controllers\Auth\AccountStatusController::class)
        ->name('akun.ditunda');

    // ---------- Login QR (perangkat yang sudah login) ----------
    Route::post('qr-login/{token}/approve', [QrLoginController::class, 'approve'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:'.config('qr-login.throttle.decide').',1')
        ->name('qr.login.approve');

    Route::post('qr-login/{token}/reject', [QrLoginController::class, 'reject'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:'.config('qr-login.throttle.decide').',1')
        ->name('qr.login.reject');

    // ---------- Perangkat Saya ----------
    Route::get('perangkat', [QrLoginController::class, 'devices'])->name('perangkat');
    Route::post('perangkat/{device}/logout', [QrLoginController::class, 'destroyDevice'])
        ->where('device', '[A-Za-z0-9]+')
        ->name('perangkat.logout');
    Route::post('perangkat/logout-all', [QrLoginController::class, 'destroyAllDevices'])->name('perangkat.logout-all');

    // Buat QR dari dashboard (per role), lalu salin tautannya ke perangkat baru.
    Route::post('perangkat/qr/buat', [QrLoginController::class, 'issue'])
        ->middleware('throttle:'.config('qr-login.throttle.start').',1')
        ->name('qr.issue');

    // Permintaan QR yang menunggu, untuk notifikasi SweetAlert2 di dashboard.
    Route::get('perangkat/qr/menunggu', [QrLoginController::class, 'pending'])
        ->middleware('throttle:60,1')
        ->name('qr.pending');
    Route::post('perangkat/qr/{id}/{aksi}', [QrLoginController::class, 'decide'])
        ->where(['id' => '[0-9]+', 'aksi' => 'approve|reject'])
        ->middleware('throttle:20,1')
        ->name('qr.decide');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
