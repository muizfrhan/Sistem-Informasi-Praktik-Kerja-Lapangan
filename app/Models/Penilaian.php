<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = [
        'mahasiswa_id', 'periode_id', 'komponen', 'dosen_id',
        'total', 'predikat', 'status', 'finalized_by', 'finalized_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PenilaianDetail::class, 'penilaian_id');
    }
}
