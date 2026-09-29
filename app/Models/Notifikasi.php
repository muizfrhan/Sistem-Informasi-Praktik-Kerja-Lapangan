<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'tipe', 'judul', 'pesan', 'data', 'tautan', 'dibaca_at'];

    protected $casts = [
        'data' => 'array',
        'dibaca_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_at');
    }

    public function scopeUntuk($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
