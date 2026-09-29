<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu permintaan login QR yang dibuat oleh perangkat baru (Device B).
 *
 * @property int $id
 * @property string $token_hash
 * @property int|null $owner_user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property int|null $approved_user_id
 */
class QrLoginSession extends Model
{
    /** Permintaan dibuat, belum ada yang membuka tautannya. */
    public const PENDING = 'pending';

    /** Perangkat baru sudah membuka tautannya dan sedang menunggu. */
    public const SCANNED = 'scanned';

    /** Halaman konfirmasi sedang ditampilkan ke user. */
    public const AWAITING_CONFIRMATION = 'awaiting_confirmation';

    /** User menyetujui — Device B tinggal mengklaim. */
    public const APPROVED = 'approved';

    /** User menolak. */
    public const REJECTED = 'rejected';

    /** Device B sudah mengambil sesi login dari QR ini. */
    public const AUTHENTICATED = 'authenticated';

    /** Sudah dipakai. */
    public const USED = 'used';

    /** Melewati masa berlaku. */
    public const EXPIRED = 'expired';

    protected $table = 'qr_login_sessions';

    protected $fillable = [
        'token_hash', 'owner_user_id', 'status', 'device_name', 'browser', 'platform', 'ip',
        'approved_user_id', 'scanned_at', 'approved_at', 'rejected_at',
        'authenticated_at', 'used_at', 'authenticated_ip', 'expires_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'scanned_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'authenticated_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }

    /** Akun yang membuat QR ini di dashboard-nya. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** True bila token masih dalam masa berlaku. */
    public function belumKedaluwarsa(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isFuture();
    }

    /** Sisa waktu dalam detik (0 kalau sudah habis). */
    public function sisaDetik(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->expires_at, false));
    }

    /** True bila QR masih menunggu keputusan user. */
    public function masihMenungguKeputusan(): bool
    {
        return in_array($this->status, [self::PENDING, self::SCANNED, self::AWAITING_CONFIRMATION], true)
            && $this->belumKedaluwarsa();
    }

    /** Selesai: sudah dipakai, ditolak, atau kedaluwarsa. */
    public function sudahSelesai(): bool
    {
        return in_array($this->status, [self::REJECTED, self::AUTHENTICATED, self::USED, self::EXPIRED], true);
    }
}
