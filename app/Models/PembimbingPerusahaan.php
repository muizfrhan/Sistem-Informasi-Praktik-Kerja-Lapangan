<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembimbingPerusahaan extends Model
{
    protected $table = 'pembimbing_perusahaan';

    protected $fillable = [
        'perusahaan_id', 'nama', 'jabatan', 'email', 'no_hp', 'foto', 'user_id', 'aktif',
    ];

    protected $casts = ['aktif' => 'boolean'];

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
