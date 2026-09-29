<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peringatan extends Model
{
    protected $table = 'peringatan';

    protected $fillable = [
        'user_id', 'mahasiswa_id', 'kode', 'judul', 'pesan', 'level', 'tautan', 'dibaca_at',
    ];

    protected $casts = ['dibaca_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_at');
    }
}
