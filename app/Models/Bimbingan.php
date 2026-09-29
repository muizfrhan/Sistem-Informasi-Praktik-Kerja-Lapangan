<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bimbingan extends Model
{
    protected $table = 'bimbingan';

    protected $fillable = [
        'mahasiswa_id', 'dosen_id', 'tanggal_bimbingan', 'catatan', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bimbingan' => 'date',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function sudahDisetujui(): bool
    {
        return $this->status === 'disetujui';
    }
}
