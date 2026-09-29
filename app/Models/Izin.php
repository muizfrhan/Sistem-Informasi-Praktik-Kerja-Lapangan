<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = [
        'mahasiswa_id', 'periode_id', 'jenis', 'tanggal_mulai', 'tanggal_selesai',
        'alasan', 'lampiran', 'status', 'diproses_oleh', 'alasan_keputusan', 'diproses_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diproses_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'izin_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }
}
