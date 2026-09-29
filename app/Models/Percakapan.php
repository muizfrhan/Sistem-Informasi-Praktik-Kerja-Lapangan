<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Percakapan extends Model
{
    protected $table = 'percakapan';

    protected $fillable = ['konteks', 'judul', 'mahasiswa_id', 'pesan_terakhir_at'];

    protected $casts = ['pesan_terakhir_at' => 'datetime'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function pesan(): HasMany
    {
        return $this->hasMany(Pesan::class, 'percakapan_id');
    }

    public function peserta(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'percakapan_peserta')
            ->withPivot('terakhir_dibaca')
            ->withTimestamps();
    }
}
