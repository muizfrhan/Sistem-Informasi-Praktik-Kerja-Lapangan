<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Folder tempat foto profil disimpan di disk `public`. */
    public const FOTO_FOLDER = 'foto-profil';

    /** Inisial untuk avatar cadangan (2 huruf). */
    protected $fillable = [
        'name', 'username', 'email', 'no_hp', 'alamat', 'foto',
        'password', 'role', 'role_id', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function roleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * URL foto profil, atau null bila belum pasang / berkasnya hilang.
     *
     *dicek ulang ke disk supaya path yang sudah dihapus manual di server
     * tidak memunculkan gambar rusak di seluruh halaman.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->foto)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto);
    }

    /** True bila pengguna sudah memasang foto profil. */
    public function hasFoto(): bool
    {
        return $this->foto_url !== null;
    }

    /** Inisial nama untuk avatar cadangan. */
    public function getInisialAttribute(): string
    {
        $name = trim((string) $this->name);

        if ($name === '') {
            return '?';
        }

        // dua kata -> ambil huruf awal masing-masing (mis. "Budi Santoso" -> BS)
        $parts = preg_split('/\s+/', $name) ?: [$name];
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(Dosen::class);
    }

    public function pembimbingPerusahaan(): HasOne
    {
        return $this->hasOne(PembimbingPerusahaan::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function peringatan(): HasMany
    {
        return $this->hasMany(Peringatan::class);
    }

    public function percakapan(): BelongsToMany
    {
        return $this->belongsToMany(Percakapan::class, 'percakapan_peserta')
            ->withPivot('terakhir_dibaca')
            ->withTimestamps();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class);
    }

    // ------------------------------------------------------------------
    // Role helper
    // ------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    public function isPembimbingPerusahaan(): bool
    {
        return $this->role === 'pembimbing_perusahaan';
    }

    /** True bila user punya salah satu dari role yang diberikan. */
    public function punyaRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function labelRole(): string
    {
        return $this->roleRecord?->label
            ?? match ($this->role) {
                'admin' => 'Administrator',
                'dosen' => 'Dosen Pembimbing',
                'mahasiswa' => 'Mahasiswa',
                'pimpinan' => 'Pimpinan',
                'koordinator' => 'Koordinator PKL',
                'pembimbing_perusahaan' => 'Pembimbing Perusahaan',
                default => ucfirst($this->role ?? '-'),
            };
    }

    /** Cegah akun nonaktif masuk sistem. */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}
