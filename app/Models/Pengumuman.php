<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'user_id', 'judul', 'isi', 'lampiran', 'target', 'pin', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'pin' => 'boolean',
    ];

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeTerbit($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeUntukRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('target', 'semua')->orWhere('target', $role);
        });
    }
}
