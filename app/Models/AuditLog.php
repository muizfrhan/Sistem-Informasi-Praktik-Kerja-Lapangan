<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $fillable = [
        'user_id', 'nama', 'role', 'action', 'module', 'keterangan', 'payload', 'ip', 'user_agent',
    ];

    protected $casts = ['payload' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Catat aktivitas penting ke audit log. */
    public static function catat(string $module, string $action, ?string $keterangan = null, array $payload = []): self
    {
        $user = Auth::user();

        return static::create([
            'user_id' => $user?->id,
            'nama' => $user?->name,
            'role' => $user?->role,
            'action' => $action,
            'module' => $module,
            'keterangan' => $keterangan,
            'payload' => $payload ?: null,
            'ip' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
        ]);
    }
}
