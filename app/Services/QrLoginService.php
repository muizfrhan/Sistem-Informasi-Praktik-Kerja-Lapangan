<?php

namespace App\Services;

use App\Exceptions\QrLoginException;
use App\Models\QrLoginSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seluruh aturan login QR: pembuatan token, transisi status, dan klaim sesi.
 *
 * Prinsip keamanan:
 *   - token dibuat dengan CSPRNG (Str::random => random_bytes), 64 karakter;
 *   - yang disimpan di database hanya SHA-256 token (one-way), sehingga bocornya
 *     isi tabel tidak Directly berarti token yang bisa dipakai;
 *   - token berumur pendek (config `qr-login.ttl`, default 60 detik);
 *   - satu token hanya bisa diklaim sekali (status `authenticated` + `used_at`);
 *   - yang 得到 sesi login adalah user yang MENGAZINKAN di perangkatnya sendiri,
 *     jadi role/permission tetap mengikuti akun tersebut.
 */
class QrLoginService
{
    /** Panjang token QR. */
    private const TOKEN_LENGTH = 64;

    /** Bersihkan permintaan lama agar tabel tidak menumpuk. */
    private const PURGE_AFTER_HOURS = 24;

    /**
     * Buat permintaan login QR baru dari dashboard pemilik akun.
     *
     * Data perangkat di sini adalah perangkat PEMILIK (yang membuat QR),
     * bukan perangkat peminta. Identitas peminta dicatat ulang di
     * `markPickedUp()` saat perangkat baru benar-benar membuka tautannya.
     *
     * @return array{record: QrLoginSession, token: string}
     */
    public function createRequest(Request $request): array
    {
        $token = Str::random(self::TOKEN_LENGTH);
        $perangkat = $this->deviceInfo((string) $request->userAgent());

        $record = QrLoginSession::create([
            'token_hash' => $this->hash($token),
            'owner_user_id' => $request->user()?->id,
            'status' => QrLoginSession::PENDING,
            'device_name' => $perangkat['device_name'],
            'browser' => $perangkat['browser'],
            'platform' => $perangkat['platform'],
            'ip' => $request->ip(),
            'expires_at' => now()->addSeconds($this->ttl()),
        ]);

        $this->purge();

        return ['record' => $record, 'token' => $token];
    }

    /** Umur token dalam detik. */
    public function ttl(): int
    {
        return max(15, (int) config('qr-login.ttl', 60));
    }

    /**
     * Permintaan QR milik `user` yang sudah DIMBUKA perangkat baru dan belum
     * kedaluwarsa.
     *
     * Syarat `scanned` itu penting: begitu pemilik menekan "Buat QR" belum ada
     * yang masuk. Notifikasi Izinkan / Tolak baru muncul setelah perangkat baru
     * benar-benar membuka tautannya — persis seperti yang dinginkan.
     *
     * `PENDING` sengaja tidak dimasukkan: QR yang baru dibuat hanya menunggu
     * belum pernah dibuka, jadi tidak perlu persetujuan siapa pun.
     */
    public function pending(User $user): ?QrLoginSession
    {
        $record = QrLoginSession::where('owner_user_id', $user->id)
            ->whereIn('status', [
                QrLoginSession::SCANNED,
                QrLoginSession::AWAITING_CONFIRMATION,
            ])
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($record) {
            return $record;
        }

        // Bersihkan sisa yang sudah lewat masa berlaku (lazy expiry).
        QrLoginSession::whereIn('status', [
            QrLoginSession::PENDING,
            QrLoginSession::SCANNED,
            QrLoginSession::AWAITING_CONFIRMATION,
        ])
            ->where('expires_at', '<=', now())
            ->update(['status' => QrLoginSession::EXPIRED]);

        return null;
    }

    /** Token -> hash (SHA-256) yang disimpan di database. */
    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Cari permintaan berdasarkan token mentah dari QR.
     * Sengaja tidak memakai `firstOrFail()` agar pemanggil bisa membedakan
     * "tidak ada" dari "sudah tidak berlaku".
     */
    public function findByToken(string $token): ?QrLoginSession
    {
        if ($token === '' || ! preg_match('/^[A-Za-z0-9]{32,128}$/', $token)) {
            return null;
        }

        return QrLoginSession::where('token_hash', $this->hash($token))->first();
    }

    /**
     * Ambil permintaan untuk ditampilkan ke user yang akan memindai.
     *
     * @throws QrLoginException
     */
    public function findScannable(string $token): QrLoginSession
    {
        $record = $this->findByToken($token);

        if (! $record) {
            throw new QrLoginException(QrLoginException::NOT_FOUND);
        }

        // Lazy expiry: token yang lewat masa berlaku langsung di-finalisasi.
        if ($record->status === QrLoginSession::PENDING && ! $record->belumKedaluwarsa()) {
            $record->forceFill(['status' => QrLoginSession::EXPIRED])->save();
        }

        if ($record->status === QrLoginSession::EXPIRED) {
            throw new QrLoginException(QrLoginException::EXPIRED);
        }

        if (in_array($record->status, [QrLoginSession::AUTHENTICATED, QrLoginSession::USED], true)) {
            throw new QrLoginException(QrLoginException::USED);
        }

        if ($record->status === QrLoginSession::REJECTED) {
            throw new QrLoginException(QrLoginException::REJECTED);
        }

        return $record;
    }

    /**
     * Perangkat baru membuka tautan QR: catat siapa pemintanya, lalu tandai
     * `scanned` supaya notifikasi Izinkan / Tolak muncul di dashboard pemilik.
     *
     * Idempoten: membuka ulang tautan tidak menimpa identitas peminta yang
     * sudah tercatat.
     */
    public function markPickedUp(QrLoginSession $record, Request $request): QrLoginSession
    {
        if ($record->status !== QrLoginSession::PENDING) {
            return $record;
        }

        $perangkat = $this->deviceInfo((string) $request->userAgent());

        $record->forceFill([
            'status' => QrLoginSession::SCANNED,
            'scanned_at' => now(),
            'device_name' => $perangkat['device_name'],
            'browser' => $perangkat['browser'],
            'platform' => $perangkat['platform'],
            'ip' => $request->ip(),
        ])->save();

        return $record;
    }

    /** Tampilkan halaman konfirmasi ke user. */
    public function markAwaitingConfirmation(QrLoginSession $record): QrLoginSession
    {
        if (in_array($record->status, [QrLoginSession::PENDING, QrLoginSession::SCANNED], true)) {
            $record->forceFill([
                'status' => QrLoginSession::AWAITING_CONFIRMATION,
                'scanned_at' => $record->scanned_at ?? now(),
            ])->save();
        }

        return $record;
    }

    /**
     * User menyetujui: Device B boleh mengambil sesi login akun ini.
     */
    public function approve(QrLoginSession $record, User $user): QrLoginSession
    {
        return DB::transaction(function () use ($record, $user) {
            $row = QrLoginSession::whereKey($record->id)->lockForUpdate()->firstOrFail();

            if ($row->status === QrLoginSession::APPROVED) {
                return $row; // idempoten
            }

            $this->assertDecidable($row);

            $row->forceFill([
                'status' => QrLoginSession::APPROVED,
                'approved_user_id' => $user->id,
                'approved_at' => now(),
                'scanned_at' => $row->scanned_at ?? now(),
            ])->save();

            return $row;
        });
    }

    /** User menolak. */
    public function reject(QrLoginSession $record): QrLoginSession
    {
        return DB::transaction(function () use ($record) {
            $row = QrLoginSession::whereKey($record->id)->lockForUpdate()->firstOrFail();

            if ($row->status === QrLoginSession::REJECTED) {
                return $row;
            }

            $this->assertDecidable($row);

            $row->forceFill([
                'status' => QrLoginSession::REJECTED,
                'rejected_at' => now(),
            ])->save();

            return $row;
        });
    }

    /**
     * Device B mengklaim sesi login.
     *
     * Dipanggil satu kali saja: state diperiksa ulang di dalam row lock supaya
     * dua tab yang memindai token sama tidak bisa sama-sama berhasil.
     *
     * @return array{user: User, record: QrLoginSession}
     *
     * @throws QrLoginException
     */
    public function claim(QrLoginSession $record, Request $request): array
    {
        // Penulisan status DI LUAR transaksi: kalau dilakukan di dalam,
        // penandaan akan hilang bersama rollback yang terjadi saat exception.
        if (! $record->belumKedaluwarsa() && ! $record->sudahSelesai()) {
            $record->forceFill(['status' => QrLoginSession::EXPIRED])->save();
        }

        return DB::transaction(function () use ($record, $request) {
            $row = QrLoginSession::whereKey($record->id)->lockForUpdate()->firstOrFail();

            if (in_array($row->status, [QrLoginSession::AUTHENTICATED, QrLoginSession::USED], true)) {
                throw new QrLoginException(QrLoginException::USED, 'QR Code sudah digunakan.');
            }

            if ($row->status === QrLoginSession::REJECTED) {
                throw new QrLoginException(QrLoginException::REJECTED, 'Login QR ditolak.');
            }

            if ($row->status === QrLoginSession::EXPIRED || ! $row->belumKedaluwarsa()) {
                throw new QrLoginException(QrLoginException::EXPIRED, 'QR Code telah kedaluwarsa.');
            }

            if ($row->status !== QrLoginSession::APPROVED || ! $row->approved_user_id) {
                throw new QrLoginException(QrLoginException::NOT_APPROVED, 'Login QR belum disetujui.');
            }

            $user = User::find($row->approved_user_id);
            if (! $user) {
                throw new QrLoginException(QrLoginException::NO_APPROVER, 'Akun pengotorisasi tidak ditemukan.');
            }

            $row->forceFill([
                'status' => QrLoginSession::USED,
                'authenticated_at' => now(),
                'used_at' => now(),
                'authenticated_ip' => $request->ip(),
            ])->save();

            return ['user' => $user, 'record' => $row];
        });
    }

    /**
     * Status untuk polling Device B — hanya data yang aman ditampilkan.
     *
     * @return array{status: string, expires_in: int, message: string}
     */
    public function clientState(QrLoginSession $record): array
    {
        $status = $record->status;
        $sisa = $record->sisaDetik();

        if ($status === QrLoginSession::PENDING || $status === QrLoginSession::SCANNED
            || $status === QrLoginSession::AWAITING_CONFIRMATION) {
            if ($sisa <= 0) {
                $record->forceFill(['status' => QrLoginSession::EXPIRED])->save();
                $status = QrLoginSession::EXPIRED;
            }
        }

        $message = match ($status) {
            QrLoginSession::PENDING => 'Menunggu tautan QR dibuka di perangkat ini…',
            QrLoginSession::SCANNED => 'Menunggu persetujuan di perangkat yang sudah login…',
            QrLoginSession::AWAITING_CONFIRMATION => 'Menunggu persetujuan di perangkat lain…',
            QrLoginSession::APPROVED => 'Disetujui. Mengautentikasi akun…',
            QrLoginSession::REJECTED => 'Login QR ditolak.',
            QrLoginSession::AUTHENTICATED, QrLoginSession::USED => 'QR Code sudah digunakan.',
            QrLoginSession::EXPIRED => 'QR Code telah kedaluwarsa.',
            default => 'Status tidak diketahui.',
        };

        return [
            'status' => $status,
            'expires_in' => $status === QrLoginSession::EXPIRED ? 0 : $sisa,
            'message' => $message,
        ];
    }

    /**
     * Hapus permintaan lama supaya tabel tidak menumpuk.
     */
    public function purge(): void
    {
        QrLoginSession::where('created_at', '<', now()->subHours(self::PURGE_AFTER_HOURS))
            ->delete();
    }

    /** QR hanya bisa diputuskan saat masih hidup dan belum diputuskan. */
    private function assertDecidable(QrLoginSession $row): void
    {
        if (! $row->belumKedaluwarsa()) {
            throw new QrLoginException(QrLoginException::EXPIRED, 'QR Code telah kedaluwarsa.');
        }

        if (in_array($row->status, [QrLoginSession::REJECTED], true)) {
            throw new QrLoginException(QrLoginException::REJECTED, 'Login QR ditolak.');
        }

        if (in_array($row->status, [QrLoginSession::AUTHENTICATED, QrLoginSession::USED], true)) {
            throw new QrLoginException(QrLoginException::USED, 'QR Code sudah digunakan.');
        }
    }

    /**
     * Pecah user-agent menjadi nama perangkat, browser, dan platform.
     *
     * @return array{device_name: string, browser: string, platform: string}
     */
    public function deviceInfo(?string $ua): array
    {
        $ua = (string) $ua;

        $browser = 'Browser tidak dikenal';
        foreach ([
            'Edg' => 'Edge',
            'OPR' => 'Opera',
            'SamsungBrowser' => 'Samsung Internet',
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari' => 'Safari',
        ] as $key => $label) {
            if (str_contains($ua, $key)) {
                $browser = $label;
                break;
            }
        }

        $platform = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Sistem tidak dikenal',
        };

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device_name' => trim($browser.' · '.$platform, ' ·'),
        ];
    }

    /**
     * Informasi lokasi yang aman: hanya jaringan lokal atau IP yang disamarkan.
     * Tidak ada panggilan layanan geolokasi pihak ketiga.
     */
    public function lokasiSingkat(?string $ip): string
    {
        if (! $ip) {
            return 'Tidak diketahui';
        }

        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return 'Perangkat ini';
        }

        if (str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')
            || str_starts_with($ip, '172.16.') || $ip === '::1') {
            return 'Jaringan lokal';
        }

        $bagian = explode('.', $ip);

        return count($bagian) === 4
            ? $bagian[0].'.'.$bagian[1].'.*.*'
            : 'Jaringan eksternal';
    }
}
