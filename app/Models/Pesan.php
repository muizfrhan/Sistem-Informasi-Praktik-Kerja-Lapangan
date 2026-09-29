<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesan extends Model
{
    protected $table = 'pesan';

    protected $fillable = ['percakapan_id', 'user_id', 'isi', 'attachment', 'dibaca_at'];

    protected $casts = ['dibaca_at' => 'datetime'];

    public function percakapan(): BelongsTo
    {
        return $this->belongsTo(Percakapan::class, 'percakapan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
